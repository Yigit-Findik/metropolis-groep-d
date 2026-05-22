//controls the mobile navigation menu and handles the logout form
export const navigation = () => ({
    open: false,
    submitLogout(event) {
        //logout links points to a form to allow for post requests
        event.preventDefault();
        event.target.closest('form').submit();
    },
    init() {
        //reset mobile menu when navagating away from page
        window.addEventListener('beforeunload', () => {
            this.open = false;
        });
    },
});
