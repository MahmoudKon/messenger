# <p align="center">Laravel Messenger</p>

<p align="center">
    <img src="https://github.com/MahmoudKon/messenger/blob/master/src/assets/messenger/images/icon.png" alt="Laravel Messenger" width="300px">
</p>

##

# Requirements

- This messenger package requires PHP ^8.0 and laravel ^9.0.
- Messenger version 7 for PHP < 8.0.
- Install pusher server and set pusher configuration in your inv file.
- Install laravel echo from  <a href='https://laravel.com/docs/9.x/broadcasting#pusher-channels'> laravel documentation </a>.
- Make enable for client events from your pusher setting.

<img src='https://github.com/MahmoudKon/messenger/blob/master/imgs/enable-client-events.PNG' alt='enable-client-events.PNG'>

##

# Installation

You can install this package via composer using:

```bash
    composer require mahmoudkon/messenger
```


For php < 8

```bash
    composer require mahmoudkon/messenger:7.0
```


Use this trait in your ``User.php`` model

```php
    ...
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Messenger\Chat\Traits\Messageable;

    class User extends Authenticatable
    {
        use Messageable;
    }
```

and run install to create assets && views && migrations files:

```php
    php artisan messenger:install
```

run migrations:

```php
    php artisan migrate
```

##

# Features


<p>
    <img src="https://github.com/MahmoudKon/messenger/blob/master/imgs/wlecome-page.PNG" alt="wlecome page">
</p>

<p>Welcome page</p>

#

<p>
    <img src="https://github.com/MahmoudKon/messenger/blob/master/imgs/profile.PNG" alt="user profile">
</p>

<p>profile for each user</p>

#

<p>
    <img src="https://github.com/MahmoudKon/messenger/blob/master/imgs/search.PNG" alt="search">
</p>

<p>search in users</p>

#

<p>
    <img src="https://github.com/MahmoudKon/messenger/blob/master/imgs/send-media.PNG" alt="send media">
</p>

<p>send Media or attachment</p>

#

<p>
    <img src="https://github.com/MahmoudKon/messenger/blob/master/imgs/typing.PNG" alt="typing">
</p>

<p>Typeing event</p>

#

<p>
    <img src="https://github.com/MahmoudKon/messenger/blob/master/imgs/not-receive-message.PNG" alt="not-receive-message">
</p>

<p>
when send message to user and he is offline, the icon will be one check icon.
when back to online the icon will be double ckeck.
When the message is read, the color of the icon will change.
</p>

#

<p>
    <img src="https://github.com/MahmoudKon/messenger/blob/master/imgs/new-message-count.PNG" alt="new-messagescount">
</p>

<p>scrolling will start at the first unread message with the count of unread messages displayed.</p>
