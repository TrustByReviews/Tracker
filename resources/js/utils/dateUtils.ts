/**
 * Date Utilities
 * 
 * Centralized date formatting functions to eliminate code duplication
 * across the application. Provides consistent date formatting for
 * different use cases.
 * 
 * @module dateUtils
 */

/**
 * Format a date string to a readable format
 * Converts date string to localized format (en-US by default)
 * 
 * @param {string | null} dateString - Date string to format
 * @param {string} locale - Locale for formatting (default: 'en-US')
 * @returns {string} Formatted date string or 'N/A' if invalid
 */
export const formatDate = (dateString: string | null, locale: string = 'en-US'): string => {
    if (!dateString) return 'N/A';
    
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Invalid date';
        
        return date.toLocaleDateString(locale, {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch (error) {
        console.warn('Error formatting date:', dateString, error);
        return 'Invalid date';
    }
};

/**
 * Format a date string to include time
 * Converts date string to localized format with time
 * 
 * @param {string | null} dateString - Date string to format
 * @param {string} locale - Locale for formatting (default: 'en-US')
 * @returns {string} Formatted date and time string or 'N/A' if invalid
 */
export const formatDateTime = (dateString: string | null, locale: string = 'en-US'): string => {
    if (!dateString) return 'N/A';
    
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Invalid date';
        
        return date.toLocaleDateString(locale, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (error) {
        console.warn('Error formatting date time:', dateString, error);
        return 'Invalid date';
    }
};

/**
 * Format a date string to a compact format
 * Converts date string to a more compact format
 * 
 * @param {string | null} dateString - Date string to format
 * @param {string} locale - Locale for formatting (default: 'en-US')
 * @returns {string} Compact formatted date string or 'N/A' if invalid
 */
export const formatDateCompact = (dateString: string | null, locale: string = 'en-US'): string => {
    if (!dateString) return 'N/A';
    
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Invalid date';
        
        return date.toLocaleDateString(locale, {
            month: 'short',
            day: 'numeric'
        });
    } catch (error) {
        console.warn('Error formatting compact date:', dateString, error);
        return 'Invalid date';
    }
};

/**
 * Format a date range (start and end dates)
 * Formats two dates as a range
 * 
 * @param {string | null} startDate - Start date string
 * @param {string | null} endDate - End date string
 * @param {string} locale - Locale for formatting (default: 'en-US')
 * @returns {string} Formatted date range string
 */
export const formatDateRange = (
    startDate: string | null, 
    endDate: string | null, 
    locale: string = 'en-US'
): string => {
    const start = formatDate(startDate, locale);
    const end = formatDate(endDate, locale);
    
    if (start === 'N/A' && end === 'N/A') return 'No dates set';
    if (start === 'N/A') return `Until ${end}`;
    if (end === 'N/A') return `From ${start}`;
    
    return `${start} → ${end}`;
};

/**
 * Check if a date is in the past
 * 
 * @param {string | null} dateString - Date string to check
 * @returns {boolean} True if date is in the past
 */
export const isDateInPast = (dateString: string | null): boolean => {
    if (!dateString) return false;
    
    try {
        const date = new Date(dateString);
        return date < new Date();
    } catch {
        return false;
    }
};

/**
 * Check if a date is today
 * 
 * @param {string | null} dateString - Date string to check
 * @returns {boolean} True if date is today
 */
export const isDateToday = (dateString: string | null): boolean => {
    if (!dateString) return false;
    
    try {
        const date = new Date(dateString);
        const today = new Date();
        
        return date.toDateString() === today.toDateString();
    } catch {
        return false;
    }
};

/**
 * Get relative time string (e.g., "2 hours ago", "3 days ago")
 * 
 * @param {string | null} dateString - Date string to format
 * @returns {string} Relative time string
 */
export const getRelativeTime = (dateString: string | null): string => {
    if (!dateString) return 'Unknown';
    
    try {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);
        
        if (diffInSeconds < 60) return 'Just now';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
        if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)} days ago`;
        if (diffInSeconds < 31536000) return `${Math.floor(diffInSeconds / 2592000)} months ago`;
        
        return `${Math.floor(diffInSeconds / 31536000)} years ago`;
    } catch (error) {
        console.warn('Error getting relative time:', dateString, error);
        return 'Unknown';
    }
};
