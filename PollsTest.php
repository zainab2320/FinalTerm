<?php
/**
 * Unit Tests for Polls
 * Tests poll validation, voting logic, and status checks
 */

require_once __DIR__ . '/TestFramework.php';
require_once __DIR__ . '/TestHelpers.php';

TestFramework::setTestClass('PollsTest');

// ============================================
// Poll Status Tests
// ============================================

TestFramework::test('Active poll is not closed', function() {
    $poll = [
        'status' => 'active',
        'end_date' => date('Y-m-d', strtotime('+7 days'))
    ];
    
    Assert::assertFalse(PollHelper::isClosed($poll));
    Assert::assertTrue(PollHelper::isActive($poll));
});

TestFramework::test('Closed poll by status is detected', function() {
    $poll = [
        'status' => 'closed',
        'end_date' => date('Y-m-d', strtotime('+7 days'))
    ];
    
    Assert::assertTrue(PollHelper::isClosed($poll));
    Assert::assertFalse(PollHelper::isActive($poll));
});

TestFramework::test('Expired poll by end_date is detected', function() {
    $poll = [
        'status' => 'active',
        'end_date' => date('Y-m-d', strtotime('-1 day'))
    ];
    
    Assert::assertTrue(PollHelper::isClosed($poll));
    Assert::assertFalse(PollHelper::isActive($poll));
});

TestFramework::test('Poll with no end_date and active status is active', function() {
    $poll = [
        'status' => 'active',
        'end_date' => null
    ];
    
    Assert::assertFalse(PollHelper::isClosed($poll));
    Assert::assertTrue(PollHelper::isActive($poll));
});

TestFramework::test('Poll with empty end_date and active status is active', function() {
    $poll = [
        'status' => 'active',
        'end_date' => ''
    ];
    
    Assert::assertFalse(PollHelper::isClosed($poll));
    Assert::assertTrue(PollHelper::isActive($poll));
});

// ============================================
// Vote Percentage Calculation Tests
// ============================================

TestFramework::test('Percentage calculation with votes', function() {
    Assert::assertEquals(50, PollHelper::calculatePercentage(5, 10));
    Assert::assertEquals(25, PollHelper::calculatePercentage(1, 4));
    Assert::assertEquals(100, PollHelper::calculatePercentage(10, 10));
    Assert::assertEquals(33, PollHelper::calculatePercentage(1, 3));
});

TestFramework::test('Percentage calculation with zero total votes', function() {
    Assert::assertEquals(0, PollHelper::calculatePercentage(0, 0));
    Assert::assertEquals(0, PollHelper::calculatePercentage(5, 0));
});

TestFramework::test('Percentage is rounded to nearest integer', function() {
    // 1/3 = 33.33... should round to 33
    Assert::assertEquals(33, PollHelper::calculatePercentage(1, 3));
    // 2/3 = 66.66... should round to 67
    Assert::assertEquals(67, PollHelper::calculatePercentage(2, 3));
});

// ============================================
// Poll Validation Tests
// ============================================

TestFramework::test('Valid poll data passes validation', function() {
    $validPoll = [
        'question' => 'What should be our next community event?',
        'options' => ['BBQ Night', 'Movie Screening', 'Sports Tournament'],
        'end_date' => date('Y-m-d', strtotime('+7 days'))
    ];
    
    $errors = PollHelper::validatePoll($validPoll);
    Assert::assertEmpty($errors, 'Valid poll should have no errors');
});

TestFramework::test('Missing question fails validation', function() {
    $poll = [
        'question' => '',
        'options' => ['Option 1', 'Option 2']
    ];
    
    $errors = PollHelper::validatePoll($poll);
    Assert::assertContains('Question is required', $errors);
});

TestFramework::test('Missing options fails validation', function() {
    $poll = [
        'question' => 'Test question?',
        'options' => []
    ];
    
    $errors = PollHelper::validatePoll($poll);
    Assert::assertContains('Poll options are required', $errors);
});

TestFramework::test('Less than 2 options fails validation', function() {
    $poll = [
        'question' => 'Test question?',
        'options' => ['Only one option']
    ];
    
    $errors = PollHelper::validatePoll($poll);
    Assert::assertContains('At least 2 poll options are required', $errors);
});

TestFramework::test('Empty options are not counted', function() {
    $poll = [
        'question' => 'Test question?',
        'options' => ['Option 1', '', '   ', 'Option 2']
    ];
    
    $errors = PollHelper::validatePoll($poll);
    Assert::assertEmpty($errors, 'Should have 2 valid options');
});

TestFramework::test('All empty options fails validation', function() {
    $poll = [
        'question' => 'Test question?',
        'options' => ['', '   ', '  ']
    ];
    
    $errors = PollHelper::validatePoll($poll);
    Assert::assertContains('At least 2 poll options are required', $errors);
});

TestFramework::test('Past end_date fails validation', function() {
    $poll = [
        'question' => 'Test question?',
        'options' => ['Option 1', 'Option 2'],
        'end_date' => date('Y-m-d', strtotime('-1 day'))
    ];
    
    $errors = PollHelper::validatePoll($poll);
    Assert::assertContains('End date must be a valid future date', $errors);
});

TestFramework::test('Invalid end_date fails validation', function() {
    $poll = [
        'question' => 'Test question?',
        'options' => ['Option 1', 'Option 2'],
        'end_date' => 'not-a-date'
    ];
    
    $errors = PollHelper::validatePoll($poll);
    Assert::assertContains('End date must be a valid future date', $errors);
});

TestFramework::test('No end_date is valid (open-ended poll)', function() {
    $poll = [
        'question' => 'Test question?',
        'options' => ['Option 1', 'Option 2'],
        'end_date' => ''
    ];
    
    $errors = PollHelper::validatePoll($poll);
    Assert::assertEmpty($errors);
});

// ============================================
// Total Votes Calculation Tests
// ============================================

TestFramework::test('Get total votes calculates correctly', function() {
    $options = [
        ['id' => 1, 'option_text' => 'Option 1', 'vote_count' => 5],
        ['id' => 2, 'option_text' => 'Option 2', 'vote_count' => 3],
        ['id' => 3, 'option_text' => 'Option 3', 'vote_count' => 7]
    ];
    
    Assert::assertEquals(15, PollHelper::getTotalVotes($options));
});

TestFramework::test('Get total votes with no votes returns 0', function() {
    $options = [
        ['id' => 1, 'option_text' => 'Option 1', 'vote_count' => 0],
        ['id' => 2, 'option_text' => 'Option 2', 'vote_count' => 0]
    ];
    
    Assert::assertEquals(0, PollHelper::getTotalVotes($options));
});

TestFramework::test('Get total votes with empty options returns 0', function() {
    Assert::assertEquals(0, PollHelper::getTotalVotes([]));
});

// ============================================
// User Vote Check Tests
// ============================================

TestFramework::test('User has voted returns true when vote exists', function() {
    $votes = [
        ['user_id' => 1, 'poll_id' => 1, 'option_id' => 2],
        ['user_id' => 2, 'poll_id' => 1, 'option_id' => 1],
        ['user_id' => 1, 'poll_id' => 2, 'option_id' => 3]
    ];
    
    Assert::assertTrue(PollHelper::hasUserVoted($votes, 1, 1));
    Assert::assertTrue(PollHelper::hasUserVoted($votes, 2, 1));
    Assert::assertTrue(PollHelper::hasUserVoted($votes, 1, 2));
});

TestFramework::test('User has voted returns false when no vote exists', function() {
    $votes = [
        ['user_id' => 1, 'poll_id' => 1, 'option_id' => 2],
        ['user_id' => 2, 'poll_id' => 1, 'option_id' => 1]
    ];
    
    Assert::assertFalse(PollHelper::hasUserVoted($votes, 3, 1));
    Assert::assertFalse(PollHelper::hasUserVoted($votes, 1, 3));
});

TestFramework::test('User has voted with empty votes returns false', function() {
    Assert::assertFalse(PollHelper::hasUserVoted([], 1, 1));
});

// ============================================
// Integration-like Tests
// ============================================

TestFramework::test('Complete poll workflow scenario', function() {
    // Create a new poll
    $pollData = [
        'question' => 'What should be our next community event?',
        'options' => ['BBQ Night', 'Movie Screening', 'Sports Tournament', 'Cultural Festival'],
        'end_date' => date('Y-m-d', strtotime('+7 days'))
    ];
    
    // Validate poll
    $errors = PollHelper::validatePoll($pollData);
    Assert::assertEmpty($errors);
    
    // Simulate poll creation
    $poll = [
        'id' => 1,
        'question' => $pollData['question'],
        'status' => 'active',
        'end_date' => $pollData['end_date'],
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Poll should be active
    Assert::assertTrue(PollHelper::isActive($poll));
    
    // Simulate options with votes
    $options = [
        ['id' => 1, 'option_text' => 'BBQ Night', 'vote_count' => 15],
        ['id' => 2, 'option_text' => 'Movie Screening', 'vote_count' => 8],
        ['id' => 3, 'option_text' => 'Sports Tournament', 'vote_count' => 12],
        ['id' => 4, 'option_text' => 'Cultural Festival', 'vote_count' => 5]
    ];
    
    // Calculate totals and percentages
    $totalVotes = PollHelper::getTotalVotes($options);
    Assert::assertEquals(40, $totalVotes);
    
    Assert::assertEquals(38, PollHelper::calculatePercentage(15, 40)); // BBQ Night
    Assert::assertEquals(20, PollHelper::calculatePercentage(8, 40));  // Movie
    Assert::assertEquals(30, PollHelper::calculatePercentage(12, 40)); // Sports
    Assert::assertEquals(13, PollHelper::calculatePercentage(5, 40));  // Cultural
});