// Syntax highlighting disabled
// Prism.js imports removed

// Alpine clipboard utility with fallback for insecure contexts
document.addEventListener('alpine:init', () => {
    Alpine.magic('clipboard', () => {
        return {
            /**
             * Copy text to clipboard with fallback for insecure contexts
             * @param {string} text - The text to copy
             * @returns {Promise<boolean>} - Returns true if successful, false otherwise
             */
            async copy(text) {
                // Try modern clipboard API first (requires secure context)
                if (navigator.clipboard && window.isSecureContext) {
                    try {
                        await navigator.clipboard.writeText(text);
                        return true;
                    } catch (err) {
                        console.warn('Clipboard API failed, trying fallback:', err);
                    }
                }

                // Fallback to execCommand for insecure contexts
                return this.copyFallback(text);
            },

            /**
             * Fallback copy method using deprecated execCommand
             * @param {string} text - The text to copy
             * @returns {boolean} - Returns true if successful, false otherwise
             */
            copyFallback(text) {
                const textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                textArea.style.top = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();

                try {
                    const successful = document.execCommand('copy');
                    textArea.remove();
                    return successful;
                } catch (err) {
                    console.error('Fallback copy failed:', err);
                    textArea.remove();
                    return false;
                }
            }
        };
    });
});
