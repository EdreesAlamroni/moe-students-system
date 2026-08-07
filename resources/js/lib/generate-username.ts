const USERNAME_LETTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
const USERNAME_DIGITS = '0123456789';

const USERNAME_LETTER_COUNT = 3;
const USERNAME_DIGIT_COUNT = 5;

function pickRandomCharacter(alphabet: string, randomValue: number): string {
    return alphabet[randomValue % alphabet.length];
}

/**
 * Generate a random 8-character username of 3 uppercase English letters and 5 digits,
 * randomly distributed throughout the username.
 *
 * Example: `A4K9P2X7`
 */
export function generateUsername(): string {
    const characters: string[] = [];
    const characterCount = USERNAME_LETTER_COUNT + USERNAME_DIGIT_COUNT;
    const randomValues = new Uint32Array(characterCount + characterCount - 1);

    crypto.getRandomValues(randomValues);

    let randomIndex = 0;

    for (let index = 0; index < USERNAME_LETTER_COUNT; index++) {
        characters.push(pickRandomCharacter(USERNAME_LETTERS, randomValues[randomIndex++]));
    }

    for (let index = 0; index < USERNAME_DIGIT_COUNT; index++) {
        characters.push(pickRandomCharacter(USERNAME_DIGITS, randomValues[randomIndex++]));
    }

    for (let index = characters.length - 1; index > 0; index--) {
        const swapIndex = randomValues[randomIndex++] % (index + 1);

        [characters[index], characters[swapIndex]] = [characters[swapIndex], characters[index]];
    }

    return characters.join('');
}


// OLD VERSION
// const USERNAME_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
// const USERNAME_LENGTH = 8;

// /**
//  * Generate a random username of uppercase English letters and digits.
//  *
//  * Example: `A4K9P2X7`
//  */
// export function generateUsername(length: number = USERNAME_LENGTH): string {
//     const characterCount = USERNAME_ALPHABET.length;
//     const randomValues = new Uint32Array(length);

//     crypto.getRandomValues(randomValues);

//     let username = '';

//     for (const value of randomValues) {
//         username += USERNAME_ALPHABET[value % characterCount];
//     }

//     return username;
// }

