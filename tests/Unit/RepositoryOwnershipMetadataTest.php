<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class RepositoryOwnershipMetadataTest extends TestCase
{
    public function test_repository_metadata_identifies_the_project_owner_and_mit_license(): void
    {
        $root = dirname(__DIR__, 2);
        $composer = json_decode(file_get_contents($root.'/composer.json'), true, flags: JSON_THROW_ON_ERROR);
        $package = json_decode(file_get_contents($root.'/package.json'), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('MIT', $composer['license']);
        $this->assertSame('Muhammad Ali Nawaz', $composer['authors'][0]['name']);
        $this->assertSame('Project owner and author', $composer['authors'][0]['role']);
        $this->assertSame('MIT', $package['license']);
        $this->assertSame('Muhammad Ali Nawaz', $package['author']);
        $this->assertFileExists($root.'/LICENSE');
    }

    public function test_removed_placeholder_ownership_reference_is_absent_from_portfolio_files(): void
    {
        $root = dirname(__DIR__, 2);
        $files = [
            $root.'/README.md',
            $root.'/NOTICE.md',
            $root.'/SECURITY.md',
            $root.'/composer.json',
            $root.'/package.json',
            $root.'/StockManager_OpenAPI.json',
        ];

        $obsoleteRepositoryOwner = base64_decode('a2h5cnI=', true);
        $obsoleteRestriction = base64_decode('cHVibGljYXRpb24gcmVzdHJpY3Rpb24=', true);

        $this->assertIsString($obsoleteRepositoryOwner);
        $this->assertIsString($obsoleteRestriction);

        foreach ($files as $file) {
            $contents = strtolower(file_get_contents($file));

            $this->assertStringNotContainsString($obsoleteRepositoryOwner, $contents);
            $this->assertStringNotContainsString($obsoleteRestriction, $contents);
        }
    }
}
