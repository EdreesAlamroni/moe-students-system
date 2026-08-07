const PASSWORD_DIGITS = '0123456789';
const PASSWORD_LENGTH = 8;

/**
 * Generate a random numeric password.
 *
 * Example: `48291037`
 */
export function generatePassword(length: number = PASSWORD_LENGTH): string {
    const digitCount = PASSWORD_DIGITS.length;
    const randomValues = new Uint32Array(length);

    crypto.getRandomValues(randomValues);

    let password = '';

    for (const value of randomValues) {
        password += PASSWORD_DIGITS[value % digitCount];
    }

    return password;
}
