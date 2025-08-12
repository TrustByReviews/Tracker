/**
 * Network utilities for robust API calls
 */

interface NetworkOptions {
  timeout?: number;
  retries?: number;
  retryDelay?: number;
}

interface ApiResponse<T = any> {
  success: boolean;
  data?: T;
  error?: string;
  status?: number;
}

/**
 * Make a robust API call with timeout, retries, and better error handling
 */
export async function robustApiCall<T = any>(
  url: string,
  options: RequestInit & NetworkOptions = {}
): Promise<ApiResponse<T>> {
  const {
    timeout = 10000,
    retries = 2,
    retryDelay = 1000,
    ...fetchOptions
  } = options;

  let lastError: Error | null = null;

  for (let attempt = 0; attempt <= retries; attempt++) {
    try {
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), timeout);

      const response = await fetch(url, {
        ...fetchOptions,
        signal: controller.signal,
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          ...fetchOptions.headers,
        },
      });

      clearTimeout(timeoutId);

      if (response.ok) {
        const data = await response.json();
        return {
          success: true,
          data,
          status: response.status,
        };
      } else {
        let errorMessage = `HTTP ${response.status}: ${response.statusText}`;
        
        // Read response body only once
        const responseText = await response.text();
        
        try {
          const errorData = JSON.parse(responseText);
          errorMessage = errorData.message || errorMessage;
        } catch {
          // If not JSON, check if it's a redirect or HTML response
          if (response.status === 302 || responseText.includes('login') || responseText.includes('<!DOCTYPE html>')) {
            errorMessage = 'Authentication required. Please log in again.';
          }
        }

        return {
          success: false,
          error: errorMessage,
          status: response.status,
        };
      }
    } catch (error) {
      lastError = error as Error;
      
      if (error instanceof Error) {
        if (error.name === 'AbortError') {
          return {
            success: false,
            error: 'Request timeout. Please try again.',
          };
        }
        
        if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
          if (attempt < retries) {
            // Wait before retrying
            await new Promise(resolve => setTimeout(resolve, retryDelay * (attempt + 1)));
            continue;
          }
          
          return {
            success: false,
            error: 'Network error: Unable to connect to server. Please check your connection and try again.',
          };
        }
      }
      
      return {
        success: false,
        error: error instanceof Error ? error.message : 'Unknown error occurred',
      };
    }
  }

  return {
    success: false,
    error: lastError?.message || 'Request failed after multiple attempts',
  };
}

/**
 * Make a POST request with robust error handling
 */
export async function robustPost<T = any>(
  url: string,
  data?: any,
  options: NetworkOptions = {}
): Promise<ApiResponse<T>> {
  return robustApiCall<T>(url, {
    method: 'POST',
    body: data ? JSON.stringify(data) : undefined,
    ...options,
  });
}

/**
 * Make a GET request with robust error handling
 */
export async function robustGet<T = any>(
  url: string,
  options: NetworkOptions = {}
): Promise<ApiResponse<T>> {
  return robustApiCall<T>(url, {
    method: 'GET',
    ...options,
  });
}

/**
 * Make a PUT request with robust error handling
 */
export async function robustPut<T = any>(
  url: string,
  data?: any,
  options: NetworkOptions = {}
): Promise<ApiResponse<T>> {
  return robustApiCall<T>(url, {
    method: 'PUT',
    body: data ? JSON.stringify(data) : undefined,
    ...options,
  });
}

/**
 * Make a DELETE request with robust error handling
 */
export async function robustDelete<T = any>(
  url: string,
  options: NetworkOptions = {}
): Promise<ApiResponse<T>> {
  return robustApiCall<T>(url, {
    method: 'DELETE',
    ...options,
  });
}
