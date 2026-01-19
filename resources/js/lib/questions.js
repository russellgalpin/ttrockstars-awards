/**
 * Shuffles an array using Fisher-Yates algorithm
 */
function shuffle(array) {
    const result = [...array];
    for (let i = result.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [result[i], result[j]] = [result[j], result[i]];
    }
    return result;
}

/**
 * Generates a unique ID for a question
 */
function generateId() {
    return Math.random().toString(36).substring(2, 9);
}

/**
 * Creates a multiplication question
 * Format: multiplier × tableNumber = answer
 * Example for 2 times table: 3 × 2 = 6
 *
 * @param {number} tableNumber - The times table number (always in middle position)
 * @param {number} multiplier - The number to multiply by (first position)
 * @param {string} userInput - Which part the user needs to fill ('operand1' or 'answer', never 'operand2')
 */
function createMultiplicationQuestion(tableNumber, multiplier, userInput = 'answer') {
    const answer = tableNumber * multiplier;
    return {
        id: generateId(),
        tableNumber,
        operand1: userInput === 'operand1' ? null : multiplier,
        operand2: tableNumber, // Always show the table number
        operator: '×',
        answer: userInput === 'answer' ? null : answer,
        correctAnswer: answer,
        correctOperand1: multiplier,
        correctOperand2: tableNumber,
        userInput,
        userValue: null,
    };
}

/**
 * Creates a division question
 * Format: dividend ÷ tableNumber = multiplier
 * Example for 8 times table: 32 ÷ 8 = 4
 *
 * @param {number} tableNumber - The times table number (divisor, always shown)
 * @param {number} multiplier - The result of the division
 * @param {string} userInput - Which part the user needs to fill ('operand1' or 'answer', never 'operand2')
 */
function createDivisionQuestion(tableNumber, multiplier, userInput = 'answer') {
    const dividend = tableNumber * multiplier;
    return {
        id: generateId(),
        tableNumber,
        operand1: userInput === 'operand1' ? null : dividend,
        operand2: tableNumber, // Always show the table number (divisor)
        operator: '÷',
        answer: userInput === 'answer' ? null : multiplier,
        correctAnswer: multiplier,
        correctOperand1: dividend,
        correctOperand2: tableNumber,
        userInput,
        userValue: null,
    };
}

/**
 * Generates Bronze level questions
 * - Tables 1-12 in sequential order
 * - Questions 1-12 in sequential order
 * - Multiplication only (format: multiplier × tableNumber = answer)
 * - User enters the answer
 */
export function generateBronzeQuestions() {
    const tables = [];

    for (let table = 1; table <= 12; table++) {
        const questions = [];
        for (let multiplier = 1; multiplier <= 12; multiplier++) {
            questions.push(createMultiplicationQuestion(table, multiplier, 'answer'));
        }
        tables.push({
            tableNumber: table,
            questions,
        });
    }

    return tables;
}

/**
 * Generates Silver level questions
 * - All 12 tables in random order
 * - Questions in random order within each table
 * - Either multiplication OR division per table (not mixed)
 * - Exactly 4 questions ask for operand1, 8 questions ask for answer
 * - Never asks for the table number (operand2)
 */
export function generateSilverQuestions() {
    const allTables = shuffle([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]);
    const tables = [];

    for (const table of allTables) {
        const isMultiplication = Math.random() < 0.5;
        const multipliers = shuffle([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]);
        const questions = [];

        // Create array of userInput types: 4 'operand1' and 8 'answer', then shuffle
        const userInputTypes = shuffle([
            'operand1',
            'operand1',
            'operand1',
            'operand1',
            'answer',
            'answer',
            'answer',
            'answer',
            'answer',
            'answer',
            'answer',
            'answer',
        ]);

        for (let i = 0; i < multipliers.length; i++) {
            const multiplier = multipliers[i];
            const userInput = userInputTypes[i];

            if (isMultiplication) {
                questions.push(createMultiplicationQuestion(table, multiplier, userInput));
            } else {
                questions.push(createDivisionQuestion(table, multiplier, userInput));
            }
        }

        tables.push({
            tableNumber: table,
            questions,
        });
    }

    return tables;
}

/**
 * Generates Gold level questions
 * - All 12 tables in random order
 * - Questions in random order within each table
 * - Multiplication AND division mixed within the same table
 * - User can enter operand1 or answer (never operand2/table number)
 */
export function generateGoldQuestions() {
    const allTables = shuffle([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]);
    const tables = [];

    for (const table of allTables) {
        const multipliers = shuffle([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]);
        const questions = [];

        for (const multiplier of multipliers) {
            const isMultiplication = Math.random() < 0.5;
            // Only allow 'operand1' or 'answer', never 'operand2' (the table number)
            const userInput = Math.random() < 0.33 ? 'operand1' : 'answer';

            if (isMultiplication) {
                questions.push(createMultiplicationQuestion(table, multiplier, userInput));
            } else {
                questions.push(createDivisionQuestion(table, multiplier, userInput));
            }
        }

        tables.push({
            tableNumber: table,
            questions,
        });
    }

    return tables;
}

/**
 * Validates a user's answer for a question
 */
export function validateAnswer(question) {
    const userValue = parseInt(question.userValue, 10);

    if (isNaN(userValue)) {
        return false;
    }

    switch (question.userInput) {
        case 'answer':
            return userValue === question.correctAnswer;
        case 'operand1':
            return userValue === question.correctOperand1;
        case 'operand2':
            return userValue === question.correctOperand2;
        default:
            return false;
    }
}

/**
 * Calculates the score for a set of tables
 */
export function calculateScore(tables) {
    let correct = 0;
    let total = 0;

    for (const table of tables) {
        for (const question of table.questions) {
            total++;
            if (validateAnswer(question)) {
                correct++;
            }
        }
    }

    return {
        correct,
        total,
        percentage: Math.round((correct / total) * 100),
    };
}
