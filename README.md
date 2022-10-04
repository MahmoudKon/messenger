# <p align="center">Laravel Messenger</p>

<p align="center">
    <img src="https://github.com/MahmoudKon/messenger/blob/master/src/assets/messenger/images/icon.png" alt="Laravel Messenger" width="300px">
</p>

##

# Requirements

- This messenger package requires PHP ^8.0 and laravel ^9.0, another version (7.0) for PHP < 8.0.
- Install pusher server
```bash
    composer require pusher/pusher-php-server
```
- set pusher configuration in your env file.
```php
    PUSHER_APP_ID=#########
    PUSHER_APP_KEY=#########
    PUSHER_APP_SECRET=#########
```
- Install laravel echo from  <a href='https://laravel.com/docs/9.x/broadcasting#client-pusher-channels'> laravel documentation </a>.
```bash
    npm install --save-dev laravel-echo pusher-js
```
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


1- Use this trait in your ``User.php`` model

```php
    ...
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Messenger\Chat\Traits\Messageable;

    class User extends Authenticatable
    {
        use Messageable;
    }
```

2- run install command to create assets && views && migrations files:

```php
    php artisan messenger:install
```

3- run migrations:

```php
    php artisan migrate
```

4-  include `` app.js `` file in the messanger/index.blade.php:

for `` mix ``

```js
    <script type="text/javascript" src="{{ asset('js/app.js') }}"></script>
```

for `` vite ``

```php
    @vite(['resources/js/app.js'])
```

##

# Configurations





##

# Features

<p>1- Welcome page to list users and conversations</p>

<p>
    <img src="https://github.com/MahmoudKon/messenger/blob/master/imgs/wlecome-page.PNG" alt="wlecome page">
</p>

#

<p>2- profile for each user</p>

<p>
    <img src="https://github.com/MahmoudKon/messenger/blob/master/imgs/profile.PNG" alt="user profile">
</p>

#

<p>3- search in users</p>

<p>
    <img src="https://github.com/MahmoudKon/messenger/blob/master/imgs/search.PNG" alt="search">
</p>

#

<p>4- send Media or attachment and download</p>

<p>
    <img src="https://github.com/MahmoudKon/messenger/blob/master/imgs/send-media.PNG" alt="send media">
</p>

#

<p>5- Typing event</p>

<p>
    <img src="https://github.com/MahmoudKon/messenger/blob/master/imgs/typing.PNG" alt="typing">
</p>


#

<p>6- scrolling will start at the first unread message with the count of unread messages displayed.</p>

<p>
    <img src="https://github.com/MahmoudKon/messenger/blob/master/imgs/new-message-count.PNG" alt="new-messagescount">
</p>

#
7- read icons
* <p> when send message to user and he is offline, the icon will be one check icon. </p>
* <p> when back to online the icon will be double ckeck. </p>
* <p> When the message is read, the color of the icon will change to green color. </p>

<p>
    <img src="https://github.com/MahmoudKon/messenger/blob/master/imgs/not-receive-message.PNG" alt="not-receive-message">
</p>