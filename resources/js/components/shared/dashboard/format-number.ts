const numberFormatter = new Intl.NumberFormat("en-US");

export default function formatNumber(value: number): string {
    return numberFormatter.format(value);
}
