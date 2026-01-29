/**
 * importExportHandler - Alpine.js component for plan migration workflows
 * 
 * Purpose: Handle file import/export, format detection, and data migration
 * 
 * Features:
 *   - Multi-format support (JSON, CSV, legacy formats)
 *   - Format detection and auto-conversion
 *   - Validation and error reporting
 *   - Preview before import
 *   - Batch import/export
 *   - Schema versioning
 *   - Conflict resolution
 * 
 * Supported Formats:
 *   - JSON (v1, v2 - native format)
 *   - CSV (legacy format)
 *   - Legacy JSON (old structure - auto-migrated)
 * 
 * Usage:
 *   <div x-data="importExportHandler()">
 *       <input @change="handleFileUpload" type="file" accept=".json,.csv">
 *   </div>
 */

export function importExportHandler() {
    return {
        // State
        importProgress: 0,
        importTotal: 0,
        importErrors: [],
        importWarnings: [],
        importSuccesses: [],
        currentFormat: null,
        previewData: null,
        isImporting: false,
        isExporting: false,
        
        // File upload handler
        async handleFileUpload(event) {
            const file = event.target.files?.[0];
            if (!file) return;
            
            try {
                this.isImporting = true;
                this.importErrors = [];
                this.importWarnings = [];
                this.importSuccesses = [];
                
                const content = await this.readFile(file);
                const format = this.detectFormat(file.name, content);
                
                if (!format) {
                    this.importErrors.push('Unable to detect file format');
                    this.$dispatch('import-failed', { error: 'Unknown format' });
                    return;
                }
                
                this.currentFormat = format;
                const parsed = this.parseContent(content, format);
                
                if (!parsed) {
                    this.importErrors.push('Failed to parse file content');
                    this.$dispatch('import-failed', { error: 'Parse error' });
                    return;
                }
                
                // Set preview
                this.previewData = parsed;
                this.$dispatch('import-preview-ready', { 
                    format, 
                    count: Array.isArray(parsed) ? parsed.length : 1,
                    preview: parsed 
                });
                
            } catch (error) {
                console.error('File upload error:', error);
                this.importErrors.push(error.message);
                this.$dispatch('import-error', { error: error.message });
            } finally {
                this.isImporting = false;
            }
        },
        
        // Read file as text
        readFile(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = (e) => resolve(e.target.result);
                reader.onerror = (e) => reject(new Error('File read failed'));
                reader.readAsText(file);
            });
        },
        
        // Detect file format
        detectFormat(filename, content) {
            const ext = filename.split('.').pop()?.toLowerCase();
            
            if (ext === 'csv') {
                return 'CSV';
            }
            
            if (ext === 'json') {
                try {
                    const parsed = JSON.parse(content);
                    if (parsed.schemaVersion === 1) return 'JSON_V1';
                    if (parsed.schemaVersion === 2) return 'JSON_V2';
                    // Legacy format (no schemaVersion)
                    if (parsed.characterName && parsed.turns) return 'JSON_LEGACY';
                    return 'JSON_UNKNOWN';
                } catch {
                    return null;
                }
            }
            
            return null;
        },
        
        // Parse content based on format
        parseContent(content, format) {
            try {
                if (format === 'CSV') {
                    return this.parseCSV(content);
                } else if (format.startsWith('JSON')) {
                    return this.parseJSON(content, format);
                }
                return null;
            } catch (error) {
                console.error('Parse error:', error);
                return null;
            }
        },
        
        // Parse JSON content
        parseJSON(content, format) {
            const data = JSON.parse(content);
            
            if (format === 'JSON_LEGACY') {
                // Migrate legacy format to current
                return this.migrateLegacyJSON(data);
            }
            
            return data;
        },
        
        // Parse CSV content
        parseCSV(content) {
            const lines = content.trim().split('\n');
            const headers = lines[0].split(',').map(h => h.trim());
            const plans = [];
            
            for (let i = 1; i < lines.length; i++) {
                const values = lines[i].split(',').map(v => v.trim());
                const plan = {};
                
                headers.forEach((header, idx) => {
                    plan[header] = values[idx];
                });
                
                plans.push(plan);
            }
            
            return plans;
        },
        
        // Migrate legacy JSON to current format
        migrateLegacyJSON(data) {
            return {
                uuid: this.generateUUID(),
                schemaVersion: 2,
                characterName: data.characterName,
                scenario: data.scenario || 'unknown',
                storageMode: 'local',
                createdAt: data.createdAt || new Date().toISOString(),
                updatedAt: new Date().toISOString(),
                turns: data.turns || [],
                skills: data.skills || [],
                stats: data.stats || {},
                notes: data.notes || '',
            };
        },
        
        // Validate plan data
        validatePlan(plan) {
            const errors = [];
            const warnings = [];
            
            // Required fields
            if (!plan.characterName) errors.push('Missing character name');
            if (!plan.turns || !Array.isArray(plan.turns)) errors.push('Invalid turns data');
            if (!plan.stats || typeof plan.stats !== 'object') warnings.push('Missing stats data');
            
            // Type validation
            if (!plan.uuid && !plan.id) warnings.push('Plan has no UUID/ID');
            if (plan.turns.length === 0) warnings.push('Plan has no training turns');
            
            // Skill validation
            if (plan.skills && Array.isArray(plan.skills)) {
                plan.skills.forEach((skill, idx) => {
                    if (!skill.id) warnings.push(`Skill ${idx} missing ID`);
                    if (!skill.name) warnings.push(`Skill ${idx} missing name`);
                });
            }
            
            return { valid: errors.length === 0, errors, warnings };
        },
        
        // Process and import plans
        async processPlanImport(plans, conflictStrategy = 'skip') {
            if (!Array.isArray(plans)) {
                plans = [plans];
            }
            
            this.importProgress = 0;
            this.importTotal = plans.length;
            
            for (const plan of plans) {
                const { valid, errors, warnings } = this.validatePlan(plan);
                
                if (!valid) {
                    this.importErrors.push(...errors);
                    continue;
                }
                
                this.importWarnings.push(...warnings);
                
                // Check for conflicts
                const existing = this.checkExistingPlan(plan.uuid);
                if (existing && conflictStrategy === 'skip') {
                    this.importWarnings.push(`Skipped duplicate plan: ${plan.characterName}`);
                    this.importProgress++;
                    continue;
                }
                
                // Save or update plan
                try {
                    await this.savePlanToStorage(plan);
                    this.importSuccesses.push(plan.characterName);
                } catch (error) {
                    this.importErrors.push(`Failed to save ${plan.characterName}: ${error.message}`);
                }
                
                this.importProgress++;
                this.$dispatch('import-progress', { 
                    current: this.importProgress, 
                    total: this.importTotal 
                });
            }
            
            this.$dispatch('import-complete', {
                successes: this.importSuccesses.length,
                errors: this.importErrors.length,
                warnings: this.importWarnings.length,
            });
        },
        
        // Export plans to JSON
        async exportPlansAsJSON(plans, filename = 'plans-export.json') {
            try {
                this.isExporting = true;
                
                const exportData = {
                    exportedAt: new Date().toISOString(),
                    schemaVersion: 2,
                    count: plans.length,
                    plans: plans.map(p => this.prepareForExport(p)),
                };
                
                const dataStr = JSON.stringify(exportData, null, 2);
                const dataBlob = new Blob([dataStr], { type: 'application/json' });
                this.downloadFile(dataBlob, filename);
                
                this.$dispatch('export-complete', { count: plans.length, filename });
                return true;
            } catch (error) {
                console.error('Export error:', error);
                this.$dispatch('export-error', { error: error.message });
                return false;
            } finally {
                this.isExporting = false;
            }
        },
        
        // Export plans to CSV
        async exportPlansAsCSV(plans, filename = 'plans-export.csv') {
            try {
                this.isExporting = true;
                
                const headers = ['UUID', 'Character', 'Scenario', 'Turns', 'Created', 'Updated'];
                const rows = plans.map(p => [
                    p.uuid,
                    p.characterName,
                    p.scenario,
                    p.turns.length,
                    p.createdAt,
                    p.updatedAt,
                ]);
                
                const csv = [
                    headers.join(','),
                    ...rows.map(r => r.map(v => `"${v}"`).join(',')),
                ].join('\n');
                
                const dataBlob = new Blob([csv], { type: 'text/csv' });
                this.downloadFile(dataBlob, filename);
                
                this.$dispatch('export-complete', { count: plans.length, filename });
                return true;
            } catch (error) {
                console.error('Export error:', error);
                this.$dispatch('export-error', { error: error.message });
                return false;
            } finally {
                this.isExporting = false;
            }
        },
        
        // Download file helper
        downloadFile(blob, filename) {
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            link.click();
            URL.revokeObjectURL(url);
        },
        
        // Prepare plan for export
        prepareForExport(plan) {
            const { uuid, schemaVersion, characterName, scenario, storageMode, createdAt, updatedAt, turns, skills, stats, notes } = plan;
            return {
                uuid,
                schemaVersion: schemaVersion || 2,
                characterName,
                scenario,
                storageMode,
                createdAt,
                updatedAt,
                turns: turns || [],
                skills: skills || [],
                stats: stats || {},
                notes: notes || '',
            };
        },
        
        // Check for existing plan (stub - implement with actual storage)
        checkExistingPlan(uuid) {
            // This would check against localStorage or server
            return false;
        },
        
        // Save plan to storage (stub - implement with actual storage)
        async savePlanToStorage(plan) {
            // This would save to localStorage or server
            return true;
        },
        
        // Generate UUID
        generateUUID() {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                const r = (Math.random() * 16) | 0;
                const v = c === 'x' ? r : (r & 0x3) | 0x8;
                return v.toString(16);
            });
        },
    };
}
