<?php

use App\Domain\Odontogram\ToothNumbering;

it('returns FDI code as string for FDI system', function (): void {
    expect(ToothNumbering::display(11, 'adult', 'fdi'))->toBe('11');
    expect(ToothNumbering::display(48, 'adult', 'fdi'))->toBe('48');
});

it('converts upper adult FDI to universal number', function (): void {
    // UPPER_ADULT = [18, 17, 16, 15, 14, 13, 12, 11, 21, 22, 23, 24, 25, 26, 27, 28]
    // Index 0 => universal 1, index 7 => universal 8, index 8 => universal 9
    expect(ToothNumbering::universalNumber(18))->toBe(1);
    expect(ToothNumbering::universalNumber(11))->toBe(8);
    expect(ToothNumbering::universalNumber(21))->toBe(9);
    expect(ToothNumbering::universalNumber(28))->toBe(16);
});

it('converts lower adult FDI to universal number', function (): void {
    // LOWER_ADULT = [48, 47, 46, 45, 44, 43, 42, 41, 31, 32, 33, 34, 35, 36, 37, 38]
    // universal = 32 - index
    // 48 => index 0 => 32, 41 => index 7 => 25, 31 => index 8 => 24, 38 => index 15 => 17
    expect(ToothNumbering::universalNumber(48))->toBe(32);
    expect(ToothNumbering::universalNumber(41))->toBe(25);
    expect(ToothNumbering::universalNumber(31))->toBe(24);
    expect(ToothNumbering::universalNumber(38))->toBe(17);
});

it('returns null for unknown FDI code', function (): void {
    expect(ToothNumbering::universalNumber(99))->toBeNull();
    expect(ToothNumbering::universalLetter(99))->toBeNull();
});

it('converts upper child FDI to universal letter', function (): void {
    // UPPER_CHILD = [55, 54, 53, 52, 51, 61, 62, 63, 64, 65]
    // Letter = chr(65 + index) => A=0, E=4, F=5, J=9
    expect(ToothNumbering::universalLetter(55))->toBe('A');
    expect(ToothNumbering::universalLetter(51))->toBe('E');
    expect(ToothNumbering::universalLetter(61))->toBe('F');
    expect(ToothNumbering::universalLetter(65))->toBe('J');
});

it('converts lower child FDI to universal letter', function (): void {
    // LOWER_CHILD = [85, 84, 83, 82, 81, 71, 72, 73, 74, 75]
    // Letter = chr(65 + (20 - index) - 1)
    // 85 => index 0 => chr(65 + 20 - 0 - 1) = chr(84) = 'T'
    // 81 => index 4 => chr(65 + 20 - 4 - 1) = chr(80) = 'P'
    // 71 => index 5 => chr(65 + 20 - 5 - 1) = chr(79) = 'O'
    // 75 => index 9 => chr(65 + 20 - 9 - 1) = chr(75) = 'K'
    expect(ToothNumbering::universalLetter(85))->toBe('T');
    expect(ToothNumbering::universalLetter(81))->toBe('P');
    expect(ToothNumbering::universalLetter(71))->toBe('O');
    expect(ToothNumbering::universalLetter(75))->toBe('K');
});

it('display returns universal number for adult teeth', function (): void {
    expect(ToothNumbering::display(18, 'adult', 'universal'))->toBe('1');
    expect(ToothNumbering::display(48, 'adult', 'universal'))->toBe('32');
});

it('display returns universal letter for child teeth', function (): void {
    expect(ToothNumbering::display(55, 'child', 'universal'))->toBe('A');
    expect(ToothNumbering::display(75, 'child', 'universal'))->toBe('K');
});
