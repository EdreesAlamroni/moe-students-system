import type { ArabicAgreementSet } from "@/lib/arabic/agreement";
import type { ArabicCountForms } from "@/lib/arabic/count";

export const arabicNouns = {
    students: {
        zero: "لا طلبة",
        one: "طالب واحد",
        two: "طالبان",
        few: "طلبة",
        other: "طالباً",
    },
    studentsInclusive: {
        zero: "لا طلبة",
        one: "طالب واحد",
        two: "طالبان",
        few: "طلبة",
        other: "طالباً",
    },
    classrooms: {
        zero: "لا فصول دراسية",
        one: "فصل دراسي واحد",
        two: "فصلان دراسيان",
        few: "فصول دراسية",
        other: "فصل دراسي",
    },
    schools: {
        zero: "لا مدارس",
        one: "مدرسة واحدة",
        two: "مدرستان",
        few: "مدارس",
        other: "مدرسة",
    },
    monitors: {
        zero: "لا مُراقبات",
        one: "مُراقبة واحدة",
        two: "مُراقبتان",
        few: "مُراقبات",
        other: "مُراقبة",
    },
    seats: {
        zero: "لا مقاعد",
        one: "مقعد واحد",
        two: "مقعدان",
        few: "مقاعد",
        other: "مقعد",
    },
    males: {
        zero: "لا ذكور",
        one: "ذكر واحد",
        two: "ذكران",
        few: "ذكور",
        other: "ذكر",
    },
    females: {
        zero: "لا إناث",
        one: "أنثى واحدة",
        two: "أنثيان",
        few: "إناث",
        other: "أنثى",
    },
    publicSchoolType: {
        zero: "لا مدارس عامة",
        one: "واحدة عامة",
        two: "عامتان",
        few: "عامة",
        other: "عامة",
    },
    privateSchoolType: {
        zero: "لا مدارس خاصة",
        one: "واحدة خاصة",
        two: "خاصتان",
        few: "خاصة",
        other: "خاصة",
    },
    publicSchoolTypeScoped: {
        zero: "لا طلبة في العامة",
        one: "واحد في العامة",
        two: "اثنان في العامة",
        few: "في العامة",
        other: "في العامة",
    },
    privateSchoolTypeScoped: {
        zero: "لا طلبة في الخاصة",
        one: "واحد في الخاصة",
        two: "اثنان في الخاصة",
        few: "في الخاصة",
        other: "في الخاصة",
    },
    deliveries: {
        zero: "لا تسليمات",
        one: "تسليم واحد",
        two: "تسليمان",
        few: "تسليمات",
        other: "تسليم",
    },
    recipients: {
        zero: "لا مستلمين",
        one: "مستلم واحد",
        two: "مستلمان",
        few: "مستلمين",
        other: "مستلم",
    },
    pendingDeliveries: {
        zero: "لا تسليمات معلّقة",
        one: "تسليم معلّق",
        two: "تسليمان معلّقان",
        few: "تسليمات معلّقة",
        other: "تسليم معلّق",
    },
} as const satisfies Record<string, ArabicCountForms>;

export const arabicParticiples: Record<string, ArabicAgreementSet> = {
    distributed: {
        nominative: {
            one: "موزع",
            two: "موزعان",
            few: "موزعون",
            other: "موزعون",
        },
        accusative: {
            one: "موزعاً",
            two: "موزعين",
            few: "موزعين",
            other: "موزعين",
        },
        genitive: {
            one: "موزع",
            two: "موزعين",
            few: "موزعين",
            other: "موزعين",
        },
    },
    assigned: {
        nominative: {
            one: "مسند",
            two: "مسندان",
            few: "مسندون",
            other: "مسندون",
        },
        accusative: {
            one: "مسنداً",
            two: "مسندين",
            few: "مسندين",
            other: "مسندين",
        },
        genitive: {
            one: "مسند",
            two: "مسندين",
            few: "مسندين",
            other: "مسندين",
        },
    },
    enrolled: {
        nominative: {
            one: "مقيد",
            two: "مقيدان",
            few: "مقيدون",
            other: "مقيدون",
        },
        accusative: {
            one: "مقيداً",
            two: "مقيدين",
            few: "مقيدين",
            other: "مقيدين",
        },
        genitive: {
            one: "مقيد",
            two: "مقيدين",
            few: "مقيدين",
            other: "مقيدين",
        },
    },
    received: {
        nominative: {
            one: "مستلم",
            two: "مستلمان",
            few: "مستلمون",
            other: "مستلمون",
        },
        accusative: {
            one: "مستلماً",
            two: "مستلمين",
            few: "مستلمين",
            other: "مستلمين",
        },
        genitive: {
            one: "مستلم",
            two: "مستلمين",
            few: "مستلمين",
            other: "مستلمين",
        },
    },
};

export const arabicVerbForms = {
    receivedBooks: {
        one: "استلم",
        two: "استلما",
        other: "استلموا",
    },
} as const;

export type DistributionTarget = keyof Pick<typeof arabicNouns, "schools" | "classrooms">;
