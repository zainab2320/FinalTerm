<?php
/**
 * Unit Tests for User Authentication
 * Tests user registration, login validation, and session management
 */

require_once __DIR__ . '/TestFramework.php';
require_once __DIR__ . '/TestHelpers.php';

TestFramework::setTestClass('UserAuthenticationTest');

// ============================================
// Email Validation Tests
// ============================================

TestFramework::test('Valid email addresses are accepted', function() {
    Assert::assertTrue(UserHelper::isValidEmail('test@example.com'));
    Assert::assertTrue(UserHelper::isValidEmail('user.name@domain.org'));
    Assert::assertTrue(UserHelper::isValidEmail('user+tag@subdomain.domain.com'));
    Assert::assertTrue(UserHelper::isValidEmail('admin@smarthub.com'));
});

TestFramework::test('Invalid email addresses are rejected', function() {
    Assert::assertFalse(UserHelper::isValidEmail(''));
    Assert::assertFalse(UserHelper::isValidEmail('notanemail'));
    Assert::assertFalse(UserHelper::isValidEmail('missing@domain'));
    Assert::assertFalse(UserHelper::isValidEmail('@nodomain.com'));
    Assert::assertFalse(UserHelper::isValidEmail('spaces in@email.com'));
});

// ============================================
// Username Validation Tests
// ============================================

TestFramework::test('Valid usernames are accepted', function() {
    Assert::assertTrue(UserHelper::isValidUsername('john'));
    Assert::assertTrue(UserHelper::isValidUsername('admin'));
    Assert::assertTrue(UserHelper::isValidUsername('john_doe'));
    Assert::assertTrue(UserHelper::isValidUsername('user123'));
});

TestFramework::test('Short usernames are rejected', function() {
    Assert::assertFalse(UserHelper::isValidUsername('ab'));
    Assert::assertFalse(UserHelper::isValidUsername('a'));
    Assert::assertFalse(UserHelper::isValidUsername(''));
});

TestFramework::test('Whitespace-only usernames are rejected', function() {
    Assert::assertFalse(UserHelper::isValidUsername('   '));
    Assert::assertFalse(UserHelper::isValidUsername('  '));
});

// ============================================
// Password Validation Tests
// ============================================

TestFramework::test('Valid passwords are accepted', function() {
    Assert::assertTrue(UserHelper::isValidPassword('password'));
    Assert::assertTrue(UserHelper::isValidPassword('admin123'));
    Assert::assertTrue(UserHelper::isValidPassword('123456'));
    Assert::assertTrue(UserHelper::isValidPassword('MyP@ssw0rd!'));
});

TestFramework::test('Short passwords are rejected', function() {
    Assert::assertFalse(UserHelper::isValidPassword('12345'));
    Assert::assertFalse(UserHelper::isValidPassword('pass'));
    Assert::assertFalse(UserHelper::isValidPassword(''));
});

TestFramework::test('Password confirmation matching works correctly', function() {
    Assert::assertTrue(UserHelper::passwordsMatch('password123', 'password123'));
    Assert::assertFalse(UserHelper::passwordsMatch('password123', 'password124'));
    Assert::assertFalse(UserHelper::passwordsMatch('password123', 'Password123'));
});

// ============================================
// Login Input Validation Tests
// ============================================

TestFramework::test('Valid login inputs are accepted', function() {
    // Email as login
    Assert::assertTrue(UserHelper::isValidLoginInput('test@example.com'));
    // Username as login
    Assert::assertTrue(UserHelper::isValidLoginInput('john_doe'));
    Assert::assertTrue(UserHelper::isValidLoginInput('admin'));
});

TestFramework::test('Invalid login inputs are rejected', function() {
    Assert::assertFalse(UserHelper::isValidLoginInput(''));
    Assert::assertFalse(UserHelper::isValidLoginInput('   '));
});

// ============================================
// Session Management Tests
// ============================================

TestFramework::test('isLoggedIn returns true when user_id exists', function() {
    $session = ['user_id' => 1, 'username' => 'admin'];
    Assert::assertTrue(UserHelper::isLoggedIn($session));
});

TestFramework::test('isLoggedIn returns false when user_id missing', function() {
    $session = ['username' => 'admin'];
    Assert::assertFalse(UserHelper::isLoggedIn($session));
    
    $emptySession = [];
    Assert::assertFalse(UserHelper::isLoggedIn($emptySession));
});

TestFramework::test('isAdmin returns true for admin users', function() {
    $adminSession = ['user_id' => 1, 'is_admin' => true];
    Assert::assertTrue(UserHelper::isAdmin($adminSession));
    
    $adminSession2 = ['user_id' => 1, 'is_admin' => 1];
    Assert::assertTrue(UserHelper::isAdmin($adminSession2));
});

TestFramework::test('isAdmin returns false for regular users', function() {
    $userSession = ['user_id' => 2, 'is_admin' => false];
    Assert::assertFalse(UserHelper::isAdmin($userSession));
    
    $userSession2 = ['user_id' => 2];
    Assert::assertFalse(UserHelper::isAdmin($userSession2));
});

// ============================================
// Input Sanitization Tests
// ============================================

TestFramework::test('Sanitize removes HTML tags', function() {
    $input = '<script>alert("xss")</script>';
    $sanitized = UserHelper::sanitize($input);
    Assert::assertNotContains('<script>', $sanitized);
});

TestFramework::test('Sanitize escapes special characters', function() {
    $input = '"><img src=x onerror=alert(1)>';
    $sanitized = UserHelper::sanitize($input);
    Assert::assertNotContains('<img', $sanitized);
});

TestFramework::test('Sanitize trims whitespace', function() {
    $input = '  hello world  ';
    $sanitized = UserHelper::sanitize($input);
    Assert::assertEquals('hello world', $sanitized);
});

// ============================================
// Mock Session Tests
// ============================================

TestFramework::test('MockSession set and get work correctly', function() {
    MockSession::clear();
    MockSession::set('user_id', 123);
    Assert::assertEquals(123, MockSession::get('user_id'));
});

TestFramework::test('MockSession returns default for missing keys', function() {
    MockSession::clear();
    Assert::assertNull(MockSession::get('nonexistent'));
    Assert::assertEquals('default', MockSession::get('nonexistent', 'default'));
});

TestFramework::test('MockSession has method works correctly', function() {
    MockSession::clear();
    Assert::assertFalse(MockSession::has('key'));
    MockSession::set('key', 'value');
    Assert::assertTrue(MockSession::has('key'));
});

TestFramework::test('MockSession remove works correctly', function() {
    MockSession::clear();
    MockSession::set('key', 'value');
    Assert::assertTrue(MockSession::has('key'));
    MockSession::remove('key');
    Assert::assertFalse(MockSession::has('key'));
});

TestFramework::test('MockSession clear removes all data', function() {
    MockSession::set('key1', 'value1');
    MockSession::set('key2', 'value2');
    MockSession::clear();
    Assert::assertEmpty(MockSession::all());
});