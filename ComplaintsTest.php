<?php
/**
 * Unit Tests for Complaints
 * Tests complaint validation and status checks
 */

require_once __DIR__ . '/TestFramework.php';
require_once __DIR__ . '/TestHelpers.php';

TestFramework::setTestClass('ComplaintsTest');

// ============================================
// Complaint Validation Tests
// ============================================

TestFramework::test('Valid complaint data passes validation', function() {
    $validComplaint = [
        'title' => 'Noise Complaint - Block A',
        'category' => 'Noise',
        'description' => 'Loud music playing late at night from apartment 301.'
    ];
    
    $errors = ComplaintHelper::validateComplaint($validComplaint);
    Assert::assertEmpty($errors, 'Valid complaint should have no errors');
});

TestFramework::test('Missing title fails validation', function() {
    $complaint = [
        'title' => '',
        'category' => 'Noise',
        'description' => 'Some description'
    ];
    
    $errors = ComplaintHelper::validateComplaint($complaint);
    Assert::assertContains('Title is required', $errors);
});

TestFramework::test('Missing category fails validation', function() {
    $complaint = [
        'title' => 'Test Complaint',
        'category' => '',
        'description' => 'Some description'
    ];
    
    $errors = ComplaintHelper::validateComplaint($complaint);
    Assert::assertContains('Valid category is required', $errors);
});

TestFramework::test('Invalid category fails validation', function() {
    $complaint = [
        'title' => 'Test Complaint',
        'category' => 'InvalidCategory',
        'description' => 'Some description'
    ];
    
    $errors = ComplaintHelper::validateComplaint($complaint);
    Assert::assertContains('Valid category is required', $errors);
});

TestFramework::test('Missing description fails validation', function() {
    $complaint = [
        'title' => 'Test Complaint',
        'category' => 'Noise',
        'description' => ''
    ];
    
    $errors = ComplaintHelper::validateComplaint($complaint);
    Assert::assertContains('Description is required', $errors);
});

TestFramework::test('All valid categories are accepted', function() {
    $categories = ['Maintenance', 'Noise', 'Parking', 'Security', 'Cleanliness', 'Facilities', 'Other'];
    
    foreach ($categories as $category) {
        $complaint = [
            'title' => 'Test',
            'category' => $category,
            'description' => 'Test description'
        ];
        
        $errors = ComplaintHelper::validateComplaint($complaint);
        Assert::assertEmpty($errors, "Category '$category' should be valid");
    }
});

// ============================================
// Status Check Tests
// ============================================

TestFramework::test('isPending returns true for Pending status', function() {
    Assert::assertTrue(ComplaintHelper::isPending('Pending'));
});

TestFramework::test('isPending returns false for other statuses', function() {
    Assert::assertFalse(ComplaintHelper::isPending('Resolved'));
    Assert::assertFalse(ComplaintHelper::isPending('Rejected'));
});

TestFramework::test('isResolved returns true for Resolved status', function() {
    Assert::assertTrue(ComplaintHelper::isResolved('Resolved'));
});

TestFramework::test('isResolved returns false for other statuses', function() {
    Assert::assertFalse(ComplaintHelper::isResolved('Pending'));
    Assert::assertFalse(ComplaintHelper::isResolved('Rejected'));
});

// ============================================
// Constants Tests
// ============================================

TestFramework::test('All complaint categories are defined', function() {
    $expectedCategories = ['Maintenance', 'Noise', 'Parking', 'Security', 'Cleanliness', 'Facilities', 'Other'];
    Assert::assertEquals($expectedCategories, ComplaintHelper::CATEGORIES);
});

TestFramework::test('All complaint statuses are defined', function() {
    $expectedStatuses = ['Pending', 'Resolved', 'Rejected'];
    Assert::assertEquals($expectedStatuses, ComplaintHelper::STATUSES);
});

// ============================================
// Multiple Validation Errors Test
// ============================================

TestFramework::test('Multiple validation errors are all returned', function() {
    $complaint = [
        'title' => '',
        'category' => 'Invalid',
        'description' => ''
    ];
    
    $errors = ComplaintHelper::validateComplaint($complaint);
    Assert::assertCount(3, $errors);
    Assert::assertContains('Title is required', $errors);
    Assert::assertContains('Valid category is required', $errors);
    Assert::assertContains('Description is required', $errors);
});