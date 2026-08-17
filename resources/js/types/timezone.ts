export type TimeZoneOption = {
    value: string;
    label: string;
    identifier: string;
    offset: string;
    search_terms: string;
};

export type TimeZoneGroup = {
    key: string;
    label: string;
    options: TimeZoneOption[];
};
