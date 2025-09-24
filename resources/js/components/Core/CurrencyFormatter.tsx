import React from 'react';

const CurrencyFormatter = ({
    amount,
    currency = 'USD',
    locale
}: {
    amount: number;
    currency?: string;
    locale?: string;
}) => {
    return new Intl.NumberFormat(locale || 'en-US', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
};

export default CurrencyFormatter;