/**
 * Formatea un número como moneda en pesos colombianos (COP)
 * @param {number} amount - Cantidad a formatear
 * @returns {string} - Moneda formateada en COP
 */
export function formatCOP(amount) {
    if (amount === null || amount === undefined || isNaN(amount)) {
        return 'COP $0,00';
    }

    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
}

/**
 * Formatea un número como moneda en pesos colombianos sin el símbolo COP
 * @param {number} amount - Cantidad a formatear
 * @returns {string} - Moneda formateada sin COP
 */
export function formatCOPWithoutSymbol(amount) {
    if (amount === null || amount === undefined || isNaN(amount)) {
        return '$0,00';
    }

    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount).replace('COP', '').trim();
}

/**
 * Formatea un número como moneda en pesos colombianos para mostrar en tablas
 * @param {number} amount - Cantidad a formatear
 * @returns {string} - Moneda formateada para tablas
 */
export function formatCOPForTable(amount) {
    if (amount === null || amount === undefined || isNaN(amount)) {
        return '$0,00';
    }

    return new Intl.NumberFormat('es-CO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
} 