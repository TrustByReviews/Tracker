/**
 * Logger Utilities
 * 
 * Centralized logging system to replace scattered console.log statements
 * and provide consistent logging across the application.
 * 
 * @module logger
 */

/**
 * Log levels for different types of messages
 */
export enum LogLevel {
    DEBUG = 'debug',
    INFO = 'info',
    WARN = 'warn',
    ERROR = 'error'
}

/**
 * Logger configuration
 */
interface LoggerConfig {
    enabled: boolean;
    level: LogLevel;
    includeTimestamp: boolean;
}

/**
 * Default logger configuration
 */
const defaultConfig: LoggerConfig = {
    enabled: true, // Will be set to false in production builds
    level: LogLevel.INFO,
    includeTimestamp: true
};

/**
 * Get current timestamp for logging
 */
const getTimestamp = (): string => {
    return new Date().toISOString();
};

/**
 * Format log message with optional timestamp
 */
const formatMessage = (level: LogLevel, message: string, data?: any): string => {
    const timestamp = defaultConfig.includeTimestamp ? `[${getTimestamp()}]` : '';
    const levelTag = `[${level.toUpperCase()}]`;
    const dataString = data ? ` | Data: ${JSON.stringify(data)}` : '';
    
    return `${timestamp} ${levelTag} ${message}${dataString}`;
};

/**
 * Check if logging is enabled for the given level
 */
const isLoggingEnabled = (level: LogLevel): boolean => {
    if (!defaultConfig.enabled) return false;
    
    const levels = Object.values(LogLevel);
    const currentLevelIndex = levels.indexOf(defaultConfig.level);
    const messageLevelIndex = levels.indexOf(level);
    
    return messageLevelIndex >= currentLevelIndex;
};

/**
 * Debug logging - for detailed debugging information
 * 
 * @param {string} message - Log message
 * @param {any} data - Optional data to log
 */
export const debug = (message: string, data?: any): void => {
    if (!isLoggingEnabled(LogLevel.DEBUG)) return;
    
    const formattedMessage = formatMessage(LogLevel.DEBUG, message, data);
    console.debug(formattedMessage);
};

/**
 * Info logging - for general information
 * 
 * @param {string} message - Log message
 * @param {any} data - Optional data to log
 */
export const info = (message: string, data?: any): void => {
    if (!isLoggingEnabled(LogLevel.INFO)) return;
    
    const formattedMessage = formatMessage(LogLevel.INFO, message, data);
    console.info(formattedMessage);
};

/**
 * Warning logging - for warnings and non-critical issues
 * 
 * @param {string} message - Log message
 * @param {any} data - Optional data to log
 */
export const warn = (message: string, data?: any): void => {
    if (!isLoggingEnabled(LogLevel.WARN)) return;
    
    const formattedMessage = formatMessage(LogLevel.WARN, message, data);
    console.warn(formattedMessage);
};

/**
 * Error logging - for errors and critical issues
 * 
 * @param {string} message - Log message
 * @param {any} data - Optional data to log
 */
export const error = (message: string, data?: any): void => {
    if (!isLoggingEnabled(LogLevel.ERROR)) return;
    
    const formattedMessage = formatMessage(LogLevel.ERROR, message, data);
    console.error(formattedMessage);
};

/**
 * Task-specific logging utilities
 */
export const taskLogger = {
    /**
     * Log task work start
     */
    workStarted: (taskId: string, userId: string): void => {
        info('Task work started', { taskId, userId });
    },
    
    /**
     * Log task work pause
     */
    workPaused: (taskId: string, userId: string): void => {
        info('Task work paused', { taskId, userId });
    },
    
    /**
     * Log task work resume
     */
    workResumed: (taskId: string, userId: string): void => {
        info('Task work resumed', { taskId, userId });
    },
    
    /**
     * Log task work finish
     */
    workFinished: (taskId: string, userId: string): void => {
        info('Task work finished', { taskId, userId });
    },
    
    /**
     * Log task assignment
     */
    taskAssigned: (taskId: string, userId: string): void => {
        info('Task assigned', { taskId, userId });
    }
};

/**
 * Bug-specific logging utilities
 */
export const bugLogger = {
    /**
     * Log bug work start
     */
    workStarted: (bugId: string, userId: string): void => {
        info('Bug work started', { bugId, userId });
    },
    
    /**
     * Log bug work pause
     */
    workPaused: (bugId: string, userId: string): void => {
        info('Bug work paused', { bugId, userId });
    },
    
    /**
     * Log bug work resume
     */
    workResumed: (bugId: string, userId: string): void => {
        info('Bug work resumed', { bugId, userId });
    },
    
    /**
     * Log bug work finish
     */
    workFinished: (bugId: string, userId: string): void => {
        info('Bug work finished', { bugId, userId });
    }
};

/**
 * Payment-specific logging utilities
 */
export const paymentLogger = {
    /**
     * Log payment report generation
     */
    reportGenerated: (reportId: string, userId: string): void => {
        info('Payment report generated', { reportId, userId });
    },
    
    /**
     * Log payment report download
     */
    reportDownloaded: (reportId: string, format: string): void => {
        info('Payment report downloaded', { reportId, format });
    }
};

/**
 * User-specific logging utilities
 */
export const userLogger = {
    /**
     * Log user login
     */
    userLoggedIn: (userId: string): void => {
        info('User logged in', { userId });
    },
    
    /**
     * Log user action
     */
    userAction: (userId: string, action: string, data?: any): void => {
        info('User action performed', { userId, action, data });
    }
};

/**
 * Configure logger settings
 * 
 * @param {Partial<LoggerConfig>} config - Configuration options
 */
export const configureLogger = (config: Partial<LoggerConfig>): void => {
    Object.assign(defaultConfig, config);
};

/**
 * Get current logger configuration
 * 
 * @returns {LoggerConfig} Current logger configuration
 */
export const getLoggerConfig = (): LoggerConfig => {
    return { ...defaultConfig };
};
