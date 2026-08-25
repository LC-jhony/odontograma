<?php

use App\Domain\Odontogram\RootAnatomy;

it('returns 1 root for incisors and canines', function (): void {
    expect(RootAnatomy::count(11))->toBe(1);
    expect(RootAnatomy::count(12))->toBe(1);
    expect(RootAnatomy::count(13))->toBe(1);
    expect(RootAnatomy::count(21))->toBe(1);
    expect(RootAnatomy::count(31))->toBe(1);
    expect(RootAnatomy::count(41))->toBe(1);
    expect(RootAnatomy::count(43))->toBe(1);
});

it('returns 2 roots for upper first premolar', function (): void {
    expect(RootAnatomy::count(14))->toBe(2);
    expect(RootAnatomy::count(24))->toBe(2);
});

it('returns 1 root for other premolars', function (): void {
    expect(RootAnatomy::count(15))->toBe(1);
    expect(RootAnatomy::count(25))->toBe(1);
    expect(RootAnatomy::count(34))->toBe(1);
    expect(RootAnatomy::count(35))->toBe(1);
    expect(RootAnatomy::count(44))->toBe(1);
    expect(RootAnatomy::count(45))->toBe(1);
});

it('returns 3 roots for upper permanent molars', function (): void {
    expect(RootAnatomy::count(16))->toBe(3);
    expect(RootAnatomy::count(17))->toBe(3);
    expect(RootAnatomy::count(18))->toBe(3);
    expect(RootAnatomy::count(26))->toBe(3);
    expect(RootAnatomy::count(27))->toBe(3);
    expect(RootAnatomy::count(28))->toBe(3);
});

it('returns 2 roots for lower permanent molars', function (): void {
    expect(RootAnatomy::count(36))->toBe(2);
    expect(RootAnatomy::count(37))->toBe(2);
    expect(RootAnatomy::count(38))->toBe(2);
    expect(RootAnatomy::count(46))->toBe(2);
    expect(RootAnatomy::count(47))->toBe(2);
    expect(RootAnatomy::count(48))->toBe(2);
});

it('returns 3 roots for upper child molars', function (): void {
    expect(RootAnatomy::count(54))->toBe(3);
    expect(RootAnatomy::count(55))->toBe(3);
    expect(RootAnatomy::count(64))->toBe(3);
    expect(RootAnatomy::count(65))->toBe(3);
});

it('returns 2 roots for lower child molars', function (): void {
    expect(RootAnatomy::count(74))->toBe(2);
    expect(RootAnatomy::count(75))->toBe(2);
    expect(RootAnatomy::count(84))->toBe(2);
    expect(RootAnatomy::count(85))->toBe(2);
});
