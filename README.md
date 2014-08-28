# ShoutIRC PHP client

This is a PHP client for the ShoutIRC remote command server. See:
http://wiki.shoutirc.com/index.php/Remote_Commands

It implments all available commands except those which require to be logged in
as DJ, as that could lead to the bot sending us unrequested responses, which
would only work well with non-blocking connections.
