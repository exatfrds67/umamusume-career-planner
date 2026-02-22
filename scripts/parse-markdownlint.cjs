#!/usr/bin/env node
/**
 * Parse markdownlint-cli2 raw output into structured JSON grouped by subdirectory.
 *
 * Usage: node scripts/parse-markdownlint.js markdownlint-raw-output.txt
 * Output: docs/lint-results.json
 */

const fs = require('fs');
const path = require('path');

const inputFile = process.argv[2] || 'markdownlint-raw-output.txt';
const outputFile = process.argv[3] || 'docs/lint-results.json';

const raw = fs.readFileSync(inputFile, 'utf8');
const lines = raw.split(/\r?\n/);

// Pattern: docs/path/file.md:LINE:COL error RULE/alias Message text
// Some rules omit COL: docs/path/file.md:LINE error RULE/alias Message text
const issueWithColRe = /^(.+?\.md):(\d+):(\d+)\s+error\s+(\S+)\s+(.+)$/;
const issueNoColRe = /^(.+?\.md):(\d+)\s+error\s+(\S+)\s+(.+)$/;

/** @type {Map<string, {line:number, col:number, rule:string, message:string}[]>} */
const fileIssues = new Map();

// Track all known .md files (from the "Linting:" header count or discovered paths)
let totalFiles = 0;
let totalErrors = 0;

for (const line of lines) {
    // Extract summary info
    const summaryMatch = line.match(/^Summary:\s+(\d+)\s+error/);
    if (summaryMatch) {
        totalErrors = parseInt(summaryMatch[1], 10);
        continue;
    }
    const lintingMatch = line.match(/^Linting:\s+(\d+)\s+file/);
    if (lintingMatch) {
        totalFiles = parseInt(lintingMatch[1], 10);
        continue;
    }

    let filePath, lineNum, col, rule, message;
    const mCol = line.match(issueWithColRe);
    if (mCol) {
        [, filePath, lineNum, col, rule, message] = mCol;
        col = parseInt(col, 10);
    } else {
        const mNoCol = line.match(issueNoColRe);
        if (!mNoCol) continue;
        [, filePath, lineNum, rule, message] = mNoCol;
        col = 0;
    }

    const normalizedPath = filePath.replace(/\\/g, '/');

    if (!fileIssues.has(normalizedPath)) {
        fileIssues.set(normalizedPath, []);
    }
    fileIssues.get(normalizedPath).push({
        line: parseInt(lineNum, 10),
        col,
        rule,
        message: message.trim(),
    });
}

// Build per-file result objects
const fileResults = [];
const filesWithIssues = new Set(fileIssues.keys());

for (const [filePath, issues] of fileIssues) {
    const ruleCounts = {};
    for (const issue of issues) {
        const ruleBase = issue.rule.split('/')[0];
        ruleCounts[ruleBase] = (ruleCounts[ruleBase] || 0) + 1;
    }
    const summaryParts = Object.entries(ruleCounts)
        .sort((a, b) => b[1] - a[1])
        .map(([rule, count]) => `${rule}: ${count}`);

    fileResults.push({
        filePath,
        issueCount: issues.length,
        issues,
        summary: summaryParts.length
            ? `${issues.length} issue(s) — ${summaryParts.join(', ')}`
            : 'clean',
    });
}

// Group by subdirectory
const dirMap = new Map();
for (const result of fileResults) {
    const dir = path.dirname(result.filePath).replace(/\\/g, '/');
    if (!dirMap.has(dir)) {
        dirMap.set(dir, { directory: dir, totalIssues: 0, fileCount: 0, children: [] });
    }
    const dirEntry = dirMap.get(dir);
    dirEntry.children.push(result);
    dirEntry.totalIssues += result.issueCount;
    dirEntry.fileCount += 1;
}

// Sort directories
const directories = Array.from(dirMap.values()).sort((a, b) =>
    a.directory.localeCompare(b.directory)
);

// Build final output
const output = {
    meta: {
        generatedAt: new Date().toISOString(),
        tool: 'markdownlint-cli2 v0.21.0',
        totalFilesLinted: totalFiles,
        totalErrors,
        filesWithIssues: filesWithIssues.size,
        directoriesWithIssues: directories.length,
    },
    directories,
};

// Rule frequency summary across all files
const globalRuleCounts = {};
for (const result of fileResults) {
    for (const issue of result.issues) {
        const ruleBase = issue.rule.split('/')[0];
        globalRuleCounts[ruleBase] = (globalRuleCounts[ruleBase] || 0) + 1;
    }
}
output.meta.ruleFrequency = Object.entries(globalRuleCounts)
    .sort((a, b) => b[1] - a[1])
    .map(([rule, count]) => ({ rule, count }));

fs.writeFileSync(outputFile, JSON.stringify(output, null, 2), 'utf8');
console.log(`✅ Written to ${outputFile}`);
console.log(`   Files with issues: ${filesWithIssues.size}`);
console.log(`   Total errors: ${totalErrors}`);
console.log(`   Directories: ${directories.length}`);
