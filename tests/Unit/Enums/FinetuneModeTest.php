<?php

declare(strict_types=1);

namespace Lanos\PHPBFL\Tests\Unit\Enums;

use Lanos\PHPBFL\Enums\FinetuneMode;
use Lanos\PHPBFL\Tests\TestCase;

/**
 * @covers \Lanos\PHPBFL\Enums\FinetuneMode
 */
class FinetuneModeTest extends TestCase
{
    public function test_enum_cases_exist(): void
    {
        $this->assertTrue(enum_exists(FinetuneMode::class));
        
        $cases = FinetuneMode::cases();
        $this->assertCount(4, $cases);
        
        $caseNames = array_map(fn($case) => $case->name, $cases);
        $expectedCases = ['GENERAL', 'CHARACTER', 'STYLE', 'PRODUCT'];
        
        $this->assertSame($expectedCases, $caseNames);
    }

    public function test_enum_values_are_correct(): void
    {
        $this->assertSame('general', FinetuneMode::GENERAL->value);
        $this->assertSame('character', FinetuneMode::CHARACTER->value);
        $this->assertSame('style', FinetuneMode::STYLE->value);
        $this->assertSame('product', FinetuneMode::PRODUCT->value);
    }

    public function test_can_create_from_string_values(): void
    {
        $this->assertSame(FinetuneMode::GENERAL, FinetuneMode::from('general'));
        $this->assertSame(FinetuneMode::CHARACTER, FinetuneMode::from('character'));
        $this->assertSame(FinetuneMode::STYLE, FinetuneMode::from('style'));
        $this->assertSame(FinetuneMode::PRODUCT, FinetuneMode::from('product'));
    }

    public function test_try_from_with_valid_values(): void
    {
        $this->assertSame(FinetuneMode::GENERAL, FinetuneMode::tryFrom('general'));
        $this->assertSame(FinetuneMode::CHARACTER, FinetuneMode::tryFrom('character'));
        $this->assertSame(FinetuneMode::STYLE, FinetuneMode::tryFrom('style'));
        $this->assertSame(FinetuneMode::PRODUCT, FinetuneMode::tryFrom('product'));
    }

    public function test_try_from_with_invalid_value(): void
    {
        $this->assertNull(FinetuneMode::tryFrom('invalid'));
        $this->assertNull(FinetuneMode::tryFrom(''));
        $this->assertNull(FinetuneMode::tryFrom('General'));
        $this->assertNull(FinetuneMode::tryFrom('GENERAL'));
    }

    public function test_from_throws_exception_with_invalid_value(): void
    {
        $this->expectException(\ValueError::class);
        FinetuneMode::from('invalid');
    }

    public function test_get_description_for_general(): void
    {
        $mode = FinetuneMode::GENERAL;
        $description = $mode->getDescription();
        
        $this->assertSame('General purpose fine-tuning for diverse content', $description);
    }

    public function test_get_description_for_character(): void
    {
        $mode = FinetuneMode::CHARACTER;
        $description = $mode->getDescription();
        
        $this->assertSame('Optimized for character-based training', $description);
    }

    public function test_get_description_for_style(): void
    {
        $mode = FinetuneMode::STYLE;
        $description = $mode->getDescription();
        
        $this->assertSame('Optimized for artistic style transfer', $description);
    }

    public function test_get_description_for_product(): void
    {
        $mode = FinetuneMode::PRODUCT;
        $description = $mode->getDescription();
        
        $this->assertSame('Optimized for product photography and commercial use', $description);
    }

    public function test_all_cases_have_descriptions(): void
    {
        foreach (FinetuneMode::cases() as $mode) {
            $description = $mode->getDescription();
            $this->assertIsString($description);
            $this->assertNotEmpty($description);
            $this->assertGreaterThan(10, strlen($description));
        }
    }

    public function test_descriptions_are_unique(): void
    {
        $descriptions = [];
        
        foreach (FinetuneMode::cases() as $mode) {
            $description = $mode->getDescription();
            $this->assertNotContains($description, $descriptions, 
                "Description for {$mode->name} is not unique: {$description}");
            $descriptions[] = $description;
        }
        
        $this->assertCount(4, array_unique($descriptions));
    }

    public function test_enum_is_instance_of_correct_type(): void
    {
        $mode = FinetuneMode::GENERAL;
        
        $this->assertInstanceOf(FinetuneMode::class, $mode);
        $this->assertInstanceOf(\UnitEnum::class, $mode);
        $this->assertInstanceOf(\BackedEnum::class, $mode);
    }

    public function test_enum_comparison(): void
    {
        $mode1 = FinetuneMode::GENERAL;
        $mode2 = FinetuneMode::GENERAL;
        $mode3 = FinetuneMode::CHARACTER;
        
        $this->assertSame($mode1, $mode2);
        $this->assertTrue($mode1 === $mode2);
        $this->assertNotSame($mode1, $mode3);
        $this->assertFalse($mode1 === $mode3);
    }

    public function test_enum_serialization(): void
    {
        $mode = FinetuneMode::STYLE;
        
        // Test that we can serialize and get the string value
        $this->assertSame('style', (string) $mode->value);
        
        // Test JSON serialization
        $json = json_encode($mode);
        $this->assertSame('"style"', $json);
    }

    public function test_enum_in_array_operations(): void
    {
        $modes = [FinetuneMode::GENERAL, FinetuneMode::STYLE];
        
        $this->assertTrue(in_array(FinetuneMode::GENERAL, $modes, true));
        $this->assertTrue(in_array(FinetuneMode::STYLE, $modes, true));
        $this->assertFalse(in_array(FinetuneMode::CHARACTER, $modes, true));
        $this->assertFalse(in_array(FinetuneMode::PRODUCT, $modes, true));
    }

    public function test_enum_switch_statement(): void
    {
        foreach (FinetuneMode::cases() as $mode) {
            $result = match ($mode) {
                FinetuneMode::GENERAL => 'general_result',
                FinetuneMode::CHARACTER => 'character_result',
                FinetuneMode::STYLE => 'style_result',
                FinetuneMode::PRODUCT => 'product_result',
            };
            
            $this->assertIsString($result);
            $this->assertStringEndsWith('_result', $result);
        }
    }

    public function test_enum_name_and_value_properties(): void
    {
        $testCases = [
            ['GENERAL', 'general'],
            ['CHARACTER', 'character'],
            ['STYLE', 'style'],
            ['PRODUCT', 'product'],
        ];

        foreach ($testCases as [$expectedName, $expectedValue]) {
            $mode = FinetuneMode::from($expectedValue);
            $this->assertSame($expectedName, $mode->name);
            $this->assertSame($expectedValue, $mode->value);
        }
    }
}