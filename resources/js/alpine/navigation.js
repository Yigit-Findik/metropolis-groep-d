// Controls the mobile navigation menu and handles the logout form submission
export const navigation = () => ({
    open: false, // Whether the mobile nav menu is visible
    submitLogout(event) {
        // The logout link points to a GET route for UX; we intercept and POST instead
        event.preventDefault();
        event.target.closest('form').submit();
    },
    init() {
        // Reset mobile menu when navigating to ensure consistent position
        window.addEventListener('beforeunload', () => {
            this.open = false;
        });
    },
});
