<?php

declare(strict_types=1);

namespace Lanos\PHPBFL\Tests\Unit\Enums;

use Lanos\PHPBFL\Enums\OutputFormat;
use Lanos\PHPBFL\Tests\TestCase;

/**
 * @covers \Lanos\PHPBFL\Enums\OutputFormat
 */
class OutputFormatTest extends TestCase
{
    public function test_enum_cases_exist(): void
    {
        $this->assertTrue(enum_exists(OutputFormat::class));
        
        $cases = OutputFormat::cases();
        $this->assertCount(2, $cases);
        
        $caseNames = array_map(fn($case) => $case->name, $cases);
        $expectedCases = ['JPEG', 'PNG'];
        
        $this->assertSame($expectedCases, $caseNames);
    }

    public function test_enum_values_are_correct(): void
    {
        $this->assertSame('jpeg', OutputFormat::JPEG->value);
        $this->assertSame('png', OutputFormat::PNG->value);
    }

    public function test_can_create_from_string_values(): void
    {
        $this->assertSame(OutputFormat::JPEG, OutputFormat::from('jpeg'));
        $this->assertSame(OutputFormat::PNG, OutputFormat::from('png'));
    }

    public function test_try_from_with_valid_values(): void
    {
        $this->assertSame(OutputFormat::JPEG, OutputFormat::tryFrom('jpeg'));
        $this->assertSame(OutputFormat::PNG, OutputFormat::tryFrom('png'));
    }

    public function test_try_from_with_invalid_value(): void
    {
        $this->assertNull(OutputFormat::tryFrom('invalid'));
        $this->assertNull(OutputFormat::tryFrom(''));
        $this->assertNull(OutputFormat::tryFrom('JPEG'));
        $this->assertNull(OutputFormat::tryFrom('PNG'));
        $this->assertNull(OutputFormat::tryFrom('jpg'));
        $this->assertNull(OutputFormat::tryFrom('gif'));
        $this->assertNull(OutputFormat::tryFrom('webp'));
    }

    public function test_from_throws_exception_with_invalid_value(): void
    {
        $this->expectException(\ValueError::class);
        OutputFormat::from('invalid');
    }

    public function test_get_mime_type_for_jpeg(): void
    {
        $format = OutputFormat::JPEG;
        $mimeType = $format->getMimeType();
        
        $this->assertSame('image/jpeg', $mimeType);
    }

    public function test_get_mime_type_for_png(): void
    {
        $format = OutputFormat::PNG;
        $mimeType = $format->getMimeType();
        
        $this->assertSame('image/png', $mimeType);
    }

    public function test_get_extension_for_jpeg(): void
    {
        $format = OutputFormat::JPEG;
        $extension = $format->getExtension();
        
        $this->assertSame('.jpg', $extension);
    }

    public function test_get_extension_for_png(): void
    {
        $format = OutputFormat::PNG;
        $extension = $format->getExtension();
        
        $this->assertSame('.png', $extension);
    }

    public function test_all_cases_have_mime_types(): void
    {
        foreach (OutputFormat::cases() as $format) {
            $mimeType = $format->getMimeType();
            $this->assertIsString($mimeType);
            $this->assertNotEmpty($mimeType);
            $this->assertStringStartsWith('image/', $mimeType);
        }
    }

    public function test_all_cases_have_extensions(): void
    {
        foreach (OutputFormat::cases() as $format) {
            $extension = $format->getExtension();
            $this->assertIsString($extension);
            $this->assertNotEmpty($extension);
            $this->assertStringStartsWith('.', $extension);
            $this->assertGreaterThan(2, strlen($extension)); // At least '.xx'
        }
    }

    public function test_mime_types_are_unique(): void
    {
        $mimeTypes = [];
        
        foreach (OutputFormat::cases() as $format) {
            $mimeType = $format->getMimeType();
            $this->assertNotContains($mimeType, $mimeTypes, 
                "MIME type for {$format->name} is not unique: {$mimeType}");
            $mimeTypes[] = $mimeType;
        }
        
        $this->assertCount(2, array_unique($mimeTypes));
    }

    public function test_extensions_are_unique(): void
    {
        $extensions = [];
        
        foreach (OutputFormat::cases() as $format) {
            $extension = $format->getExtension();
            $this->assertNotContains($extension, $extensions, 
                "Extension for {$format->name} is not unique: {$extension}");
            $extensions[] = $extension;
        }
        
        $this->assertCount(2, array_unique($extensions));
    }

    public function test_enum_is_instance_of_correct_type(): void
    {
        $format = OutputFormat::JPEG;
        
        $this->assertInstanceOf(OutputFormat::class, $format);
        $this->assertInstanceOf(\UnitEnum::class, $format);
        $this->assertInstanceOf(\BackedEnum::class, $format);
    }

    public function test_enum_comparison(): void
    {
        $format1 = OutputFormat::JPEG;
        $format2 = OutputFormat::JPEG;
        $format3 = OutputFormat::PNG;
        
        $this->assertSame($format1, $format2);
        $this->assertTrue($format1 === $format2);
        $this->assertNotSame($format1, $format3);
        $this->assertFalse($format1 === $format3);
    }

    public function test_enum_serialization(): void
    {
        $format = OutputFormat::PNG;
        
        // Test that we can serialize and get the string value
        $this->assertSame('png', (string) $format->value);
        
        // Test JSON serialization
        $json = json_encode($format);
        $this->assertSame('"png"', $json);
    }

    public function test_enum_in_array_operations(): void
    {
        $formats = [OutputFormat::JPEG];
        
        $this->assertTrue(in_array(OutputFormat::JPEG, $formats, true));
        $this->assertFalse(in_array(OutputFormat::PNG, $formats, true));
    }

    public function test_enum_switch_statement(): void
    {
        foreach (OutputFormat::cases() as $format) {
            $result = match ($format) {
                OutputFormat::JPEG => 'jpeg_result',
                OutputFormat::PNG => 'png_result',
            };
            
            $this->assertIsString($result);
            $this->assertStringEndsWith('_result', $result);
        }
    }

    public function test_enum_name_and_value_properties(): void
    {
        $testCases = [
            ['JPEG', 'jpeg'],
            ['PNG', 'png'],
        ];

        foreach ($testCases as [$expectedName, $expectedValue]) {
            $format = OutputFormat::from($expectedValue);
            $this->assertSame($expectedName, $format->name);
            $this->assertSame($expectedValue, $format->value);
        }
    }

    public function test_mime_type_matches_standard_formats(): void
    {
        // Verify that MIME types follow standard conventions
        $this->assertSame('image/jpeg', OutputFormat::JPEG->getMimeType());
        $this->assertSame('image/png', OutputFormat::PNG->getMimeType());
        
        // Ensure they don't use alternative forms like 'image/jpg'
        $this->assertNotSame('image/jpg', OutputFormat::JPEG->getMimeType());
    }

    public function test_extensions_are_commonly_used(): void
    {
        // Verify extensions match common conventions
        $this->assertSame('.jpg', OutputFormat::JPEG->getExtension()); // Most common JPEG extension
        $this->assertSame('.png', OutputFormat::PNG->getExtension());
        
        // Ensure they don't use alternative forms
        $this->assertNotSame('.jpeg', OutputFormat::JPEG->getExtension());
    }

    public function test_format_consistency(): void
    {
        // Test that the enum value, MIME type, and extension are logically consistent
        foreach (OutputFormat::cases() as $format) {
            $value = $format->value;
            $mimeType = $format->getMimeType();
            $extension = $format->getExtension();
            
            // MIME type should contain the format value
            $this->assertStringContainsString($value, $mimeType);
            
            // Extension should be related to the format
            if ($format === OutputFormat::JPEG) {
                $this->assertTrue(in_array($extension, ['.jpg', '.jpeg']));
                $this->assertSame('image/jpeg', $mimeType);
            }
            
            if ($format === OutputFormat::PNG) {
                $this->assertSame('.png', $extension);
                $this->assertSame('image/png', $mimeType);
            }
        }
    }

    public function test_can_be_used_in_file_operations(): void
    {
        // Test that formats can be used for practical file operations
        foreach (OutputFormat::cases() as $format) {
            $filename = 'test_image' . $format->getExtension();
            $this->assertStringEndsWith($format->getExtension(), $filename);
            
            // Verify the extension can be used for path operations
            $pathInfo = pathinfo($filename);
            $this->assertSame(ltrim($format->getExtension(), '.'), $pathInfo['extension']);
        }
    }
}