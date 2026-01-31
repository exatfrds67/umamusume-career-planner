// Historical tracking page logic
async function clearHistoricalCache() {
    try {
        const response = await fetch(
            window.location.pathname + "/api/clear-cache",
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN":
                        document.querySelector('meta[name="csrf-token"]')
                            ?.content || "",
                },
            },
        );

        if (response.ok) {
            window.location.reload();
        } else {
            alert("Failed to clear cache. Please try again.");
        }
    } catch (error) {
        console.error("Error clearing cache:", error);
        alert("An error occurred. Please try again.");
    }
}

// Export for global access
window.clearHistoricalCache = clearHistoricalCache;
