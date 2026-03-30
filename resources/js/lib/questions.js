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
 * @param {string} userInput - Which part the user needs to fill ('operand1', 'operand2', or 'answer')
 */
function createMultiplicationQuestion(tableNumber, multiplier, userInput = 'answer') {
    const answer = tableNumber * multiplier;
    return {
        id: generateId(),
        tableNumber,
        operand1: userInput === 'operand1' ? null : multiplier,
        operand2: userInput === 'operand2' ? null : tableNumber,
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
 * @param {string} userInput - Which part the user needs to fill ('operand1', 'operand2', or 'answer')
 */
function createDivisionQuestion(tableNumber, multiplier, userInput = 'answer') {
    const dividend = tableNumber * multiplier;
    return {
        id: generateId(),
        tableNumber,
        operand1: userInput === 'operand1' ? null : dividend,
        operand2: userInput === 'operand2' ? null : tableNumber,
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
 * - 5 sets of 10 questions = 50 total
 * - Each question draws from a random times table (1-12)
 * - Multiplication and division mixed within each set
 * - 3 operand1 + 3 operand2 + 4 answer input types per set (shuffled)
 */
export function generateGoldQuestions() {
    const tables = [];

    // Build pool of all possible questions and shuffle to guarantee uniqueness
    const pool = [];
    for (let table = 1; table <= 12; table++) {
        for (let multiplier = 1; multiplier <= 12; multiplier++) {
            pool.push({ tableNumber: table, multiplier, isMultiplication: true });
            pool.push({ tableNumber: table, multiplier, isMultiplication: false });
        }
    }
    const shuffledPool = shuffle(pool);

    let poolIndex = 0;

    for (let set = 1; set <= 5; set++) {
        const questions = [];

        const userInputTypes = shuffle([
            'operand1',
            'operand1',
            'operand1',
            'operand2',
            'operand2',
            'operand2',
            'answer',
            'answer',
            'answer',
            'answer',
        ]);

        for (let i = 0; i < 10; i++) {
            const { tableNumber, multiplier, isMultiplication } = shuffledPool[poolIndex++];
            const userInput = userInputTypes[i];

            if (isMultiplication) {
                questions.push(createMultiplicationQuestion(tableNumber, multiplier, userInput));
            } else {
                questions.push(createDivisionQuestion(tableNumber, multiplier, userInput));
            }
        }

        tables.push({
            tableNumber: set,
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
    let answered = 0;

    for (const table of tables) {
        for (const question of table.questions) {
            total++;
            const hasAnswer = question.userValue !== null && question.userValue !== undefined && String(question.userValue).trim() !== '';
            if (hasAnswer) {
                answered++;
                if (validateAnswer(question)) {
                    correct++;
                }
            }
        }
    }

    const incorrect = answered - correct;
    const unanswered = total - answered;

    return {
        correct,
        total,
        answered,
        incorrect,
        unanswered,
        percentage: Math.round((correct / total) * 100),
    };
}
