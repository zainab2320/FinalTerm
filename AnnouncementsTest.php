<?php
/**
 * Unit Tests for Announcements
 * Tests announcement validation, permissions, and view tracking
 */

require_once __DIR__ . '/TestFramework.php';
require_once __DIR__ . '/TestHelpers.php';

TestFramework::setTestClass('AnnouncementsTest');

// ============================================
// Announcement Validation Tests
// ============================================

TestFramework::test('Valid announcement data passes validation', function() {
    $validAnnouncement = [
        'title' => 'Community Meeting Notice',
        'category' => 'General',
        'content' => 'There will be a community meeting this Saturday at 5 PM.'
    ];
    
    $errors = AnnouncementHelper::validateAnnouncement($validAnnouncement);
    Assert::assertEmpty($errors, 'Valid announcement should have no errors');
});

TestFramework::test('Missing title fails validation', function() {
    $announcement = [
        'title' => '',
        'category' => 'General',
        'content' => 'Some content'
    ];
    
    $errors = AnnouncementHelper::validateAnnouncement($announcement);
    Assert::assertContains('Title is required', $errors);
});

TestFramework::test('Missing category fails validation', function() {
    $announcement = [
        'title' => 'Test Title',
        'category' => '',
        'content' => 'Some content'
    ];
    
    $errors = AnnouncementHelper::validateAnnouncement($announcement);
    Assert::assertContains('Valid category is required', $errors);
});

TestFramework::test('Invalid category fails validation', function() {
    $announcement = [
        'title' => 'Test Title',
        'category' => 'InvalidCategory',
        'content' => 'Some content'
    ];
    
    $errors = AnnouncementHelper::validateAnnouncement($announcement);
    Assert::assertContains('Valid category is required', $errors);
});

TestFramework::test('Missing content fails validation', function() {
    $announcement = [
        'title' => 'Test Title',
        'category' => 'General',
        'content' => ''
    ];
    
    $errors = AnnouncementHelper::validateAnnouncement($announcement);
    Assert::assertContains('Content is required', $errors);
});

TestFramework::test('All valid categories are accepted', function() {
    $categories = ['General', 'Events', 'Maintenance', 'Safety', 'Community', 'Other'];
    
    foreach ($categories as $category) {
        $announcement = [
            'title' => 'Test',
            'category' => $category,
            'content' => 'Test content'
        ];
        
        $errors = AnnouncementHelper::validateAnnouncement($announcement);
        Assert::assertEmpty($errors, "Category '$category' should be valid");
    }
});

// ============================================
// Permission Tests
// ============================================

TestFramework::test('Admin can delete any announcement', function() {
    $announcement = ['id' => 1, 'created_by' => 2];
    $adminUser = ['id' => 1, 'is_admin' => true];
    
    Assert::assertTrue(AnnouncementHelper::canDelete($announcement, $adminUser));
});

TestFramework::test('Creator can delete their own announcement', function() {
    $announcement = ['id' => 1, 'created_by' => 2];
    $creator = ['id' => 2, 'is_admin' => false];
    
    Assert::assertTrue(AnnouncementHelper::canDelete($announcement, $creator));
});

TestFramework::test('Non-admin cannot delete others announcement', function() {
    $announcement = ['id' => 1, 'created_by' => 2];
    $otherUser = ['id' => 3, 'is_admin' => false];
    
    Assert::assertFalse(AnnouncementHelper::canDelete($announcement, $otherUser));
});

TestFramework::test('User with no admin flag cannot delete others announcement', function() {
    $announcement = ['id' => 1, 'created_by' => 2];
    $otherUser = ['id' => 3]; // No is_admin key
    
    Assert::assertFalse(AnnouncementHelper::canDelete($announcement, $otherUser));
});

// ============================================
// Date Formatting Tests
// ============================================

TestFramework::test('Date formatting works correctly', function() {
    Assert::assertEquals('Jan 15, 2024', AnnouncementHelper::formatDate('2024-01-15'));
    Assert::assertEquals('Dec 25, 2024', AnnouncementHelper::formatDate('2024-12-25'));
    Assert::assertEquals('Jun 01, 2024', AnnouncementHelper::formatDate('2024-06-01'));
});

TestFramework::test('Date formatting with datetime string', function() {
    Assert::assertEquals('Jan 15, 2024', AnnouncementHelper::formatDate('2024-01-15 10:30:00'));
});

// ============================================
// View Counter Tests
// ============================================

TestFramework::test('Increment views works correctly', function() {
    Assert::assertEquals(1, AnnouncementHelper::incrementViews(0));
    Assert::assertEquals(101, AnnouncementHelper::incrementViews(100));
    Assert::assertEquals(1000, AnnouncementHelper::incrementViews(999));
});

// ============================================
// Constants Tests
// ============================================

TestFramework::test('All announcement categories are defined', function() {
    $expectedCategories = ['General', 'Events', 'Maintenance', 'Safety', 'Community', 'Other'];
    Assert::assertEquals($expectedCategories, AnnouncementHelper::CATEGORIES);
});

TestFramework::test('All announcement statuses are defined', function() {
    $expectedStatuses = ['draft', 'published'];
    Assert::assertEquals($expectedStatuses, AnnouncementHelper::STATUSES);
});

// ============================================
// Multiple Validation Errors Test
// ============================================

TestFramework::test('Multiple validation errors are all returned', function() {
    $announcement = [
        'title' => '',
        'category' => 'Invalid',
        'content' => ''
    ];
    
    $errors = AnnouncementHelper::validateAnnouncement($announcement);
    Assert::assertGreaterThan(1, count($errors));
    Assert::assertContains('Title is required', $errors);
    Assert::assertContains('Valid category is required', $errors);
    Assert::assertContains('Content is required', $errors);
});