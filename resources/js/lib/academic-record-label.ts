const ARABIC_ORDINAL_YEARS: Record<number, string> = {
    1: 'الأولى',
    2: 'الثانية',
    3: 'الثالثة',
    4: 'الرابعة',
    5: 'الخامسة',
    6: 'السادسة',
    7: 'السابعة',
    8: 'الثامنة',
    9: 'التاسعة',
    10: 'العاشرة',
    11: 'الحادية عشرة',
    12: 'الثانية عشرة',
};

export function academicRecordYearLabel(attemptNumber: number): string {
    const ordinal = ARABIC_ORDINAL_YEARS[attemptNumber];

    if (ordinal) {
        return `السنة ${ordinal}`;
    }

    return `السنة ${attemptNumber}`;
}
