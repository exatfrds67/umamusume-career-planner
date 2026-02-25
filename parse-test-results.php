<?php

$xml = simplexml_load_file('test-results-latest.xml');
echo "Tests: {$xml['tests']} | Failures: {$xml['failures']} | Errors: {$xml['errors']}\n";

foreach ($xml->testsuite as $suite) {
    foreach ($suite->testcase as $tc) {
        if (isset($tc->failure)) {
            echo "FAIL: {$suite['name']} > {$tc['name']}\n";
            echo '  '.substr((string) $tc->failure, 0, 200)."\n";
        }
    }
}
