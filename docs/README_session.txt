Session variables are used to store user data and pass that information between pages. For servers
that host multiple websites there could be conflicts between websites that use the same session
name. To prevent this problem we have added a check in the theme/admin_header.php page to detect if the user switches to another instance of the same website. When this is detected the session variables are cleared.
