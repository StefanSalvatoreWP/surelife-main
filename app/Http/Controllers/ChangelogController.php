<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/* 2024 SilverDust) S. Maceren */

class ChangelogController extends Controller
{
    /**
     * Parse CHANGELOG.md and return structured data
     */
    public function index()
    {
        $changelogPath = base_path('CHANGELOG.md');
        
        if (!file_exists($changelogPath)) {
            return view('pages.home', ['changelog' => [], 'error' => 'Changelog file not found']);
        }
        
        $content = file_get_contents($changelogPath);
        $versions = $this->parseChangelog($content);
        
        // Find the first actual version (skip Unreleased)
        $latestVersion = null;
        foreach ($versions as $version) {
            if ($version['version'] !== 'Unreleased') {
                $latestVersion = $version;
                break;
            }
        }
        
        return view('pages.home', [
            'changelog' => $versions,
            'latestVersion' => $latestVersion
        ]);
    }
    
    /**
     * Parse changelog content into structured array
     */
    private function parseChangelog(string $content): array
    {
        $versions = [];
        $lines = explode("\n", $content);
        
        $currentVersion = null;
        $currentSection = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip empty lines and header
            if (empty($line) || str_starts_with($line, '# Changelog') || str_starts_with($line, 'All notable') || str_starts_with($line, 'The format is') || str_starts_with($line, 'and this project')) {
                continue;
            }
            
            // Version header: ## [1.7.0] - 2024-01-15 or ## [Unreleased]
            if (preg_match('/^## \[(.*?)\](?: - (\d{4}-\d{2}-\d{2}))?/', $line, $matches)) {
                if ($currentVersion) {
                    $versions[] = $currentVersion;
                }
                $currentVersion = [
                    'version' => $matches[1],
                    'date' => $matches[2] ?? null,
                    'sections' => []
                ];
                $currentSection = null;
                continue;
            }
            
            // Section header: ### Added, ### Fixed, etc.
            if (preg_match('/^### (\w+)/', $line, $matches)) {
                $currentSection = strtolower($matches[1]);
                $currentVersion['sections'][$currentSection] = [];
                continue;
            }
            
            // Change item: - Description
            if (str_starts_with($line, '- ') && $currentSection && $currentVersion) {
                $item = substr($line, 2);
                $currentVersion['sections'][$currentSection][] = $this->formatItem($item);
            }
        }
        
        // Add last version
        if ($currentVersion) {
            $versions[] = $currentVersion;
        }
        
        return $versions;
    }
    
    /**
     * Format a changelog item for display
     */
    private function formatItem(string $item): array
    {
        // Check for bold prefix (e.g., **Activity Log** - Description)
        $hasBold = preg_match('/^\*\*(.+?)\*\* - (.+)$/', $item, $matches);
        
        if ($hasBold) {
            return [
                'title' => $matches[1],
                'description' => $matches[2],
                'has_bold' => true
            ];
        }
        
        // Check for inline code or bold text
        $item = preg_replace('/`([^`]+)`/', '<code class="bg-gray-100 px-1 rounded text-sm">$1</code>', $item);
        $item = preg_replace('/\*\*([^*]+)\*\*/', '<strong class="font-semibold">$1</strong>', $item);
        
        return [
            'description' => $item,
            'has_bold' => false
        ];
    }
    
    /**
     * Get current version from config
     */
    public static function getCurrentVersion(): string
    {
        return config('app.version', '1.7.1');
    }
}
