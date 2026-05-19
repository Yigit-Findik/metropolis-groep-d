export const navigation = () => ({
    open: false,
    submitLogout(event) {
        event.preventDefault();
        event.target.closest('form').submit();
    },
});
