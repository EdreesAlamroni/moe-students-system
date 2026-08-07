const USERNAME_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
const USERNAME_LENGTH = 8;

/**
 * Generate a random username of uppercase English letters and digits.
 *
 * Example: `A4K9P2X7`
 */
export function generateUsername(length: number = USERNAME_LENGTH): string {
    const characterCount = USERNAME_ALPHABET.length;
    const randomValues = new Uint32Array(length);

    crypto.getRandomValues(randomValues);

    let username = '';

    for (const value of randomValues) {
        username += USERNAME_ALPHABET[value % characterCount];
    }

    return username;
}
