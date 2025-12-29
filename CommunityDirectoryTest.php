<?php
/**
 * Unit Tests for Community Directory
 * Tests profile validation, search, and visibility filtering
 */

require_once __DIR__ . '/TestFramework.php';
require_once __DIR__ . '/TestHelpers.php';

TestFramework::setTestClass('CommunityDirectoryTest');

// ============================================
// Profile Validation Tests
// ============================================

TestFramework::test('Valid profile data passes validation', function() {
    $validProfile = [
        'name' => 'John Doe',
        'phone' => '0300-1234567',
        'block' => 'A',
        'unit' => '101'
    ];
    
    $errors = DirectoryHelper::validateProfile($validProfile);
    Assert::assertEmpty($errors, 'Valid profile should have no errors');
});

TestFramework::test('Profile with only name passes validation', function() {
    $profile = [
        'name' => 'John Doe'
    ];
    
    $errors = DirectoryHelper::validateProfile($profile);
    Assert::assertEmpty($errors);
});

TestFramework::test('Missing name fails validation', function() {
    $profile = [
        'name' => '',
        'phone' => '0300-1234567'
    ];
    
    $errors = DirectoryHelper::validateProfile($profile);
    Assert::assertContains('Name is required', $errors);
});

TestFramework::test('Valid phone formats are accepted', function() {
    $validPhones = [
        '0300-1234567',
        '03001234567',
        '+92-300-1234567',
        '(0300) 1234567',
        '+923001234567'
    ];
    
    foreach ($validPhones as $phone) {
        $profile = ['name' => 'Test', 'phone' => $phone];
        $errors = DirectoryHelper::validateProfile($profile);
        Assert::assertEmpty($errors, "Phone '$phone' should be valid");
    }
});

TestFramework::test('Invalid phone format fails validation', function() {
    $profile = [
        'name' => 'John Doe',
        'phone' => 'not-a-phone'
    ];
    
    $errors = DirectoryHelper::validateProfile($profile);
    Assert::assertContains('Invalid phone number format', $errors);
});

TestFramework::test('Phone with letters fails validation', function() {
    $profile = [
        'name' => 'John Doe',
        'phone' => '0300-ABC-1234'
    ];
    
    $errors = DirectoryHelper::validateProfile($profile);
    Assert::assertContains('Invalid phone number format', $errors);
});

TestFramework::test('Empty phone is valid (optional field)', function() {
    $profile = [
        'name' => 'John Doe',
        'phone' => ''
    ];
    
    $errors = DirectoryHelper::validateProfile($profile);
    Assert::assertEmpty($errors);
});

// ============================================
// Search Filtering Tests
// ============================================

TestFramework::test('Filter by name works correctly', function() {
    $profiles = [
        ['name' => 'John Doe', 'profession' => 'Engineer', 'skills' => 'Python'],
        ['name' => 'Jane Smith', 'profession' => 'Designer', 'skills' => 'UI/UX'],
        ['name' => 'John Smith', 'profession' => 'Manager', 'skills' => 'Leadership']
    ];
    
    $filtered = DirectoryHelper::filterBySearch($profiles, 'John');
    Assert::assertCount(2, $filtered);
});

TestFramework::test('Filter by profession works correctly', function() {
    $profiles = [
        ['name' => 'John Doe', 'profession' => 'Software Engineer', 'skills' => 'Python'],
        ['name' => 'Jane Smith', 'profession' => 'Designer', 'skills' => 'UI/UX'],
        ['name' => 'Bob Wilson', 'profession' => 'Engineer', 'skills' => 'Java']
    ];
    
    $filtered = DirectoryHelper::filterBySearch($profiles, 'Engineer');
    Assert::assertCount(2, $filtered);
});

TestFramework::test('Filter by skills works correctly', function() {
    $profiles = [
        ['name' => 'John', 'profession' => 'Dev', 'skills' => 'Python, JavaScript'],
        ['name' => 'Jane', 'profession' => 'Dev', 'skills' => 'Java, Python'],
        ['name' => 'Bob', 'profession' => 'Designer', 'skills' => 'Photoshop']
    ];
    
    $filtered = DirectoryHelper::filterBySearch($profiles, 'Python');
    Assert::assertCount(2, $filtered);
});

TestFramework::test('Search is case-insensitive', function() {
    $profiles = [
        ['name' => 'JOHN DOE', 'profession' => 'Engineer', 'skills' => 'Python'],
        ['name' => 'jane doe', 'profession' => 'Designer', 'skills' => 'UI']
    ];
    
    $filtered = DirectoryHelper::filterBySearch($profiles, 'doe');
    Assert::assertCount(2, $filtered);
});

TestFramework::test('Empty search returns all profiles', function() {
    $profiles = [
        ['name' => 'John', 'profession' => 'Dev', 'skills' => 'Python'],
        ['name' => 'Jane', 'profession' => 'Designer', 'skills' => 'UI']
    ];
    
    $filtered = DirectoryHelper::filterBySearch($profiles, '');
    Assert::assertCount(2, $filtered);
});

TestFramework::test('Search handles missing optional fields', function() {
    $profiles = [
        ['name' => 'John Doe'],
        ['name' => 'Jane Smith', 'profession' => 'Developer'],
        ['name' => 'Bob Wilson', 'skills' => 'Python']
    ];
    
    $filtered = DirectoryHelper::filterBySearch($profiles, 'Developer');
    Assert::assertCount(1, $filtered);
});

// ============================================
// Visibility Filtering Tests
// ============================================

TestFramework::test('Filter visible only shows visible profiles', function() {
    $profiles = [
        ['name' => 'John', 'is_visible' => true],
        ['name' => 'Jane', 'is_visible' => false],
        ['name' => 'Bob', 'is_visible' => true],
        ['name' => 'Alice', 'is_visible' => 1]
    ];
    
    $visible = DirectoryHelper::filterVisible($profiles);
    Assert::assertCount(3, $visible);
});

TestFramework::test('Filter visible handles missing is_visible field', function() {
    $profiles = [
        ['name' => 'John', 'is_visible' => true],
        ['name' => 'Jane'], // Missing is_visible
        ['name' => 'Bob', 'is_visible' => false]
    ];
    
    $visible = DirectoryHelper::filterVisible($profiles);
    Assert::assertCount(1, $visible);
});

TestFramework::test('Filter visible with all invisible returns empty', function() {
    $profiles = [
        ['name' => 'John', 'is_visible' => false],
        ['name' => 'Jane', 'is_visible' => false]
    ];
    
    $visible = DirectoryHelper::filterVisible($profiles);
    Assert::assertEmpty($visible);
});

// ============================================
// Combined Search and Visibility Tests
// ============================================

TestFramework::test('Combined search and visibility filtering', function() {
    $profiles = [
        ['name' => 'John Developer', 'profession' => 'Dev', 'is_visible' => true],
        ['name' => 'Jane Developer', 'profession' => 'Dev', 'is_visible' => false],
        ['name' => 'Bob Designer', 'profession' => 'Design', 'is_visible' => true]
    ];
    
    // First filter by visibility
    $visible = DirectoryHelper::filterVisible($profiles);
    // Then search
    $result = DirectoryHelper::filterBySearch($visible, 'Developer');
    
    Assert::assertCount(1, $result);
});