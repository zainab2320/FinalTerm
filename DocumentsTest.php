<?php
/**
 * Unit Tests for Documents
 * Tests file validation, size formatting, and filename generation
 */

require_once __DIR__ . '/TestFramework.php';
require_once __DIR__ . '/TestHelpers.php';

TestFramework::setTestClass('DocumentsTest');

// ============================================
// File Validation Tests
// ============================================

TestFramework::test('Valid PDF file passes validation', function() {
    $file = [
        'name' => 'community_rules.pdf',
        'size' => 1024 * 1024 // 1MB
    ];
    
    $errors = DocumentHelper::validateFile($file);
    Assert::assertEmpty($errors, 'Valid PDF should pass validation');
});

TestFramework::test('Valid DOC file passes validation', function() {
    $file = [
        'name' => 'meeting_minutes.doc',
        'size' => 500 * 1024 // 500KB
    ];
    
    $errors = DocumentHelper::validateFile($file);
    Assert::assertEmpty($errors, 'Valid DOC should pass validation');
});

TestFramework::test('Valid DOCX file passes validation', function() {
    $file = [
        'name' => 'report.docx',
        'size' => 2 * 1024 * 1024 // 2MB
    ];
    
    $errors = DocumentHelper::validateFile($file);
    Assert::assertEmpty($errors);
});

TestFramework::test('Valid Excel files pass validation', function() {
    $xlsFile = ['name' => 'budget.xls', 'size' => 1024 * 1024];
    $xlsxFile = ['name' => 'budget.xlsx', 'size' => 1024 * 1024];
    
    Assert::assertEmpty(DocumentHelper::validateFile($xlsFile));
    Assert::assertEmpty(DocumentHelper::validateFile($xlsxFile));
});

TestFramework::test('Valid PowerPoint files pass validation', function() {
    $pptFile = ['name' => 'presentation.ppt', 'size' => 1024 * 1024];
    $pptxFile = ['name' => 'presentation.pptx', 'size' => 1024 * 1024];
    
    Assert::assertEmpty(DocumentHelper::validateFile($pptFile));
    Assert::assertEmpty(DocumentHelper::validateFile($pptxFile));
});

TestFramework::test('Valid TXT file passes validation', function() {
    $file = [
        'name' => 'notes.txt',
        'size' => 100 * 1024 // 100KB
    ];
    
    $errors = DocumentHelper::validateFile($file);
    Assert::assertEmpty($errors);
});

TestFramework::test('Missing filename fails validation', function() {
    $file = [
        'name' => '',
        'size' => 1024
    ];
    
    $errors = DocumentHelper::validateFile($file);
    Assert::assertContains('File is required', $errors);
});

TestFramework::test('Invalid file extension fails validation', function() {
    $file = [
        'name' => 'virus.exe',
        'size' => 1024
    ];
    
    $errors = DocumentHelper::validateFile($file);
    Assert::assertNotEmpty($errors);
    Assert::assertTrue(strpos($errors[0], 'Invalid file type') !== false);
});

TestFramework::test('PHP file extension fails validation', function() {
    $file = [
        'name' => 'script.php',
        'size' => 1024
    ];
    
    $errors = DocumentHelper::validateFile($file);
    Assert::assertNotEmpty($errors);
});

TestFramework::test('Image file extension fails validation', function() {
    $file = [
        'name' => 'photo.jpg',
        'size' => 1024
    ];
    
    $errors = DocumentHelper::validateFile($file);
    Assert::assertNotEmpty($errors);
});

TestFramework::test('File exceeding 10MB fails validation', function() {
    $file = [
        'name' => 'large_file.pdf',
        'size' => 11 * 1024 * 1024 // 11MB
    ];
    
    $errors = DocumentHelper::validateFile($file);
    Assert::assertContains('File size exceeds maximum limit of 10MB', $errors);
});

TestFramework::test('File exactly at 10MB passes validation', function() {
    $file = [
        'name' => 'max_size.pdf',
        'size' => 10 * 1024 * 1024 // Exactly 10MB
    ];
    
    $errors = DocumentHelper::validateFile($file);
    Assert::assertEmpty($errors);
});

// ============================================
// File Size Formatting Tests
// ============================================

TestFramework::test('Bytes formatting works correctly', function() {
    Assert::assertEquals('500 bytes', DocumentHelper::formatFileSize(500));
    Assert::assertEquals('1023 bytes', DocumentHelper::formatFileSize(1023));
});

TestFramework::test('Kilobytes formatting works correctly', function() {
    Assert::assertEquals('1 KB', DocumentHelper::formatFileSize(1024));
    Assert::assertEquals('1.5 KB', DocumentHelper::formatFileSize(1536));
    Assert::assertEquals('500 KB', DocumentHelper::formatFileSize(512000));
});

TestFramework::test('Megabytes formatting works correctly', function() {
    Assert::assertEquals('1 MB', DocumentHelper::formatFileSize(1048576));
    Assert::assertEquals('2.5 MB', DocumentHelper::formatFileSize(2621440));
    Assert::assertEquals('10 MB', DocumentHelper::formatFileSize(10485760));
});

// ============================================
// Safe Filename Generation Tests
// ============================================

TestFramework::test('Safe filename removes special characters', function() {
    $filename = DocumentHelper::generateSafeFilename('my file@#$.pdf');
    Assert::assertMatchesRegex('/^my_file____\d+\.pdf$/', $filename);
});

TestFramework::test('Safe filename preserves extension', function() {
    $filename = DocumentHelper::generateSafeFilename('document.docx');
    Assert::assertContains('.docx', $filename);
});

TestFramework::test('Safe filename adds timestamp', function() {
    $before = time();
    $filename = DocumentHelper::generateSafeFilename('test.pdf');
    $after = time();
    
    // Extract timestamp from filename
    preg_match('/(\d+)\.pdf$/', $filename, $matches);
    $timestamp = (int)$matches[1];
    
    Assert::assertTrue($timestamp >= $before && $timestamp <= $after);
});

TestFramework::test('Safe filename handles spaces', function() {
    $filename = DocumentHelper::generateSafeFilename('my document file.pdf');
    Assert::assertNotContains(' ', $filename);
});

// ============================================
// Constants Tests
// ============================================

TestFramework::test('All allowed extensions are defined', function() {
    $expectedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
    Assert::assertEquals($expectedExtensions, DocumentHelper::ALLOWED_EXTENSIONS);
});

TestFramework::test('Max file size is 10MB', function() {
    Assert::assertEquals(10 * 1024 * 1024, DocumentHelper::MAX_FILE_SIZE);
});

TestFramework::test('All document categories are defined', function() {
    $expectedCategories = [
        'Rules & Regulations',
        'Forms',
        'Meeting Minutes',
        'Financial Reports',
        'Notices',
        'Other'
    ];
    Assert::assertEquals($expectedCategories, DocumentHelper::CATEGORIES);
});

// ============================================
// Edge Cases
// ============================================

TestFramework::test('Case-insensitive extension check', function() {
    $file1 = ['name' => 'document.PDF', 'size' => 1024];
    $file2 = ['name' => 'document.Pdf', 'size' => 1024];
    $file3 = ['name' => 'document.DOCX', 'size' => 1024];
    
    Assert::assertEmpty(DocumentHelper::validateFile($file1));
    Assert::assertEmpty(DocumentHelper::validateFile($file2));
    Assert::assertEmpty(DocumentHelper::validateFile($file3));
});

TestFramework::test('Filename with multiple dots handled correctly', function() {
    $file = [
        'name' => 'my.document.v2.final.pdf',
        'size' => 1024
    ];
    
    $errors = DocumentHelper::validateFile($file);
    Assert::assertEmpty($errors);
});