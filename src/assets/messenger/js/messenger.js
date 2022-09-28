let jqXHR = {abort: function () {}}; // init empty object
let next_page  = 1;
let next_messages_page = 1;
let conversation_user_id = null;

$(function() {
    $('body').on('click', '.user-room', function(e) {
        e.preventDefault();
        let btn = $(this);
        $('.user-room').not(btn).removeClass('open-chat');
        btn.addClass('open-chat');
        $.ajax({
            url: btn.attr('href'),
            type: "get",
            success: function(response, textStatus, jqXHR) {
                $('#load-chat').empty().append(response.view);
                conversation_user_id = btn.data('user-id');
                next_messages_page = response.next_page;
                conversation_id = response.conversation.id;

                if (parseInt(btn.find('.unread-messages').text()) > 0) {
                    changeReadMessageIcon(btn.data('user-id'), 'read');
                    // make event [ unread-count ] on the fly to make sync between unread count messages in chat with dashboard
                    chatChannel.whisper('unread-count', {
                        auth_id: AUTH_USER_ID,
                        count: Number.parseInt( $('#all-unread-messages').text() )
                    }).whisper('seen-message', {
                        user_id: AUTH_USER_ID,
                        auth_id: btn.data('user-id')
                    });

                    changeCounter(`#all-unread-messages`, btn.find('.unread-messages').text(), '-');
                    btn.find('.unread-messages').text(0).addClass('d-none');
                }

                let scrollTop = 0;
                if (response.messages.data && response.messages.data.length) { // load latest read messages
                    $.each(response.messages.data, function (key, message) {
                        $('body').find('[data-conversation-user]').prepend(messageTemplate(message, message.user_id == AUTH_USER_ID ? 'message-out' : ''));
                    });

                    $('#load-chat .chat-body').animate({scrollTop: $('#load-chat .chat-body').prop("scrollHeight")}, 200);

                    scrollTop = $('body').find('[data-conversation-user] .message:last').offset().top;
                } else if (response.unread_messages.length == 0) {
                    $('body').find('[data-conversation-user]').prepend(emptyChatTemplate());
                }

                if (response.unread_messages.length) {
                    $('body').find('[data-conversation-user]').append(newMessagesTemplate(response.unread_messages.length));
                    $('#load-chat .chat-body').animate({scrollTop: scrollTop}, 1);

                    $.each(response.unread_messages, function (key, message) {
                        $('body').find('[data-conversation-user]').append(messageTemplate(message, message.user_id == AUTH_USER_ID ? 'message-out' : ''));
                    });
                }
            }
        });
    });

    $('body').on('submit', '#send-message', function(e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            data: new FormData($(this)[0]),
            dataType: 'JSON',
            processData: false,
            contentType: false,
            success: function(response, textStatus, jqXHR) {
                $('#empty-chat, #empty-conversations').remove();
                $('#load-chat').find('.message-divider').remove();
                $('[name="message"]').val('');
                $('[name="file"]').val('');
                changeReadMessageIcon($('[name="user_id"]').val(), 'send');
                reOrder(response.message, response.user_id);
                $('#load-chat').find(`[data-conversation-user='${response.user_id}']`).append(messageTemplate(response.message, 'message-out'));
                $('#load-chat .chat-body').animate({scrollTop: $('#load-chat .chat-body').prop("scrollHeight")}, 100);
            }
        });
    });


    $('body').on('change', '#input-file', function() {
        $('#send-message').submit();
    });


    $('body').on('click', '[data-bs-target]', function(e) {
        e.preventDefault();
        let btn = $(this);
        let target = $(`${btn.data('bs-target')}`).find('.modal-content');
        target.empty();
        $.ajax({
            url: btn.attr('href'),
            type: "get",
            success: function (response, textStatus, jqXHR) {
                target.append(response);
            }
        });
    });

    $('body').on('keyup', 'input#search', function(e) {
        loadConversations(1, {search: $(this).val()}, true);
    });

    $('#tab-content-chats .hide-scrollbar').scroll(function () {
        if ( $(this).scrollTop() + $(this).innerHeight() == $(this)[0].scrollHeight && next_page !== null)
            loadConversations(next_page, {search: $('input#search').val()});
    });


    $('body').on('keyup', 'input#users-search', function(e) {
        loadUsers(1, {search: $('input#users-search').val()}, true);
    });

    $('#tab-content-friends .hide-scrollbar').scroll(function () {
        if ( $(this).scrollTop() + $(this).innerHeight() == $(this)[0].scrollHeight && next_page !== null)
            loadUsers(next_page, {search: $('input#users-search').val()});
    });


    let time = false;
    $('body').on('keydown', '[name="message"]', function(){
        if (event.keyCode > 90 || event.keyCode < 65) return;

        chatChannel.whisper('typing', {
            typing: true,
            auth_id: AUTH_USER_ID,
            user_id: $('input[name="user_id"]').val()
        });

        if (time) clearTimeout(time);
        time = setTimeout( () => {
            chatChannel.whisper('typing', {
                typing: false,
                auth_id: AUTH_USER_ID,
                user_id: $('input[name="user_id"]').val()
            });
        }, 500);
    });


    $('#tab-friends').click(function() {
        loadUsers(1, {}, true);
    });


    $('#tab-chats').click(function() {
        loadConversations(1, {}, true);
    });


/**********************************************************************************************************************************************************************
//! SECTION **************************************************************** PUSHER Functions *************************************************************************
**********************************************************************************************************************************************************************/
    // To get message from pusher and append it
    window.Echo.private(`new-message.${AUTH_USER_ID}`)
                .listen('\\Messenger\\Chat\\Events\\MessageCreated', (data) => {
                    $('body').find(`[data-conversation-user="${conversation_user_id}"]`).find('.user-typing').remove();
                    $('#empty-conversations').remove();
                    reOrder(data.message, data.message.user_id);
                    let conversation_body = $('body').find(`[data-conversation-user="${data.message.user_id}"]`);

                    if (conversation_body.length == 0) {
                        changeCounter(`.unread-messages-user-${data.message.user_id}`);
                        changeCounter(`#all-unread-messages`);
                        changeReadMessageIcon(data.message.user_id, 'receive');

                        chatChannel.whisper('load-message', {
                            user_id: AUTH_USER_ID,
                            auth_id: data.message.user_id
                        });

                        try { audio.play(); } catch (error) {}
                        return;
                    }

                    $('#load-chat').find('.message-divider').remove();

                    $('#empty-chat').remove();
                    if ($('body').find('input[name="conversation_id"').val())
                        makeReadAll($('body').find('input[name="conversation_id"').val());

                    chatChannel.whisper('seen-message', {
                        user_id: AUTH_USER_ID,
                        auth_id: data.message.user_id
                    });

                    conversation_body.append(messageTemplate(data.message));
                    changeReadMessageIcon(data.message.user_id, 'read');

                    $('#load-chat .chat-body').animate({scrollTop: $('#load-chat .chat-body').prop("scrollHeight")}, 100);
                });

    let chatChannel = window.Echo.join(`chat`)
                            .joining((user) => { // This user is join to chat page
                                $('body').find(`.online-status-${user.id}`).addClass('avatar-online');
                                $('body').find(`.online-status-${user.id}-text`).text('Online');

                                if ($(`[data-user-id="${user.id}"]`).find('.d-none.send-message-icon').length == 0) {
                                    changeReadMessageIcon(user.id, 'receive');
                                }
                            })
                            .leaving((user) => { // This user is leaving to chat page
                                $('body').find(`.online-status-${user.id}`).removeClass('avatar-online');
                                $('body').find(`.online-status-${user.id}-text`).text('Offline');
                                toggleTyping(false, user.id);
                                toggleTypingInChat(false, user.id);
                                updateLastActive(user.id);
                            })
                            .listenForWhisper('typing', (e) => {
                                if (AUTH_USER_ID != e.user_id) return;
                                toggleTyping(e.typing, e.auth_id);
                                toggleTypingInChat(e.typing, e.auth_id);
                                $('#load-chat .chat-body').animate({scrollTop: $('#load-chat .chat-body').prop("scrollHeight")}, 100);
                            })
                            .listenForWhisper('seen-message', (e) => {
                                if (AUTH_USER_ID != e.auth_id) return;
                                changeReadMessageIcon(e.user_id, 'read');
                            })
                            .listenForWhisper('load-message', (e) => {
                                if (AUTH_USER_ID != e.auth_id) return;
                                changeReadMessageIcon(e.user_id, 'receive');
                            });


/**********************************************************************************************************************************************************************
//? LINK **************************************************************** Helper Functions ****************************************************************************
**********************************************************************************************************************************************************************/

    let tabContentType = null;
    function loadConversations(page = 1, data = {}, empty = false) {
        tabContentType = $('#tab-content-chats');
        loadData(`?page=${page}`, data, empty)
    }

    function loadUsers(page = 1, data = {}, empty = false) {
        tabContentType = $('#tab-content-friends');
        loadData(`users?page=${page}`, data, empty)
    }

    function loadData(url = '', data = {}, empty = false) {
        jqXHR.abort();
        jqXHR = $.ajax({
            url: window.location.href+'/'+url,
            type: "GET",
            data: data,
            success: function (response) {
                next_page = response.next_page;
                if (empty) tabContentType.find('.conversations-list').empty();
                tabContentType.find('.conversations-list').append(response.view);
            }
        });
    }

    function loadMoreMessages(conversation, user_id) {
        let chat_window = $('body').find('#load-chat .hide-scrollbar');
        $.ajax({
            url: window.location.href+`/conversation/${conversation}/messages/load-more?page=${next_messages_page}`,
            type: "GET",
            success: function (response) {
                let scrollHeight = chat_window[0].scrollHeight;
                next_messages_page = response.next_page;
                $.each(response.messages.data, function (key, message) {
                    $('body').find(`[data-conversation-user=${user_id}]`).prepend(messageTemplate(message, message.user_id == AUTH_USER_ID ? 'message-out' : ''));
                });

                chat_window.animate({scrollTop: chat_window[0].scrollHeight - scrollHeight}, 1);
            }
        });
    }

    function updateLastActive(id) {
        $.ajax({
            url: window.location.href+'/update/last-seen',
            type: "get",
            data: {user_id: id},
            success: function (response, textStatus, jqXHR) {}
        });
    }

    function makeReadAll(conversation_id) {
        $.ajax({
            url: window.location.href+'/update/read-at',
            type: "get",
            data: {conversation_id: conversation_id},
            success: function (response, textStatus, jqXHR) {}
        });
    }


    // Reorder conversation according last send message
    function reOrder(message, user_id) {
        let ele = $('body').find(`.conversations-list .user-room[data-user-id="${user_id}"]`);
        let sender = message.user_id == AUTH_USER_ID ? 'You: ' : `${message.user.name}: `;

        let msg = message.message;
        if (message.type != 'text') {
            let type = message.type.split('/')[0];
            type = type == 'application' || type == 'text' ? 'Attachment' : type;
            msg = `Send ${type}`;
        }

        ele.find('.last-message').text(sender + ' ' + msg);
        ele.find('.message-time').text(message.created_at);
        tabContentType.find(`.conversations-list .user-room[data-user-id="${user_id}"]`).remove();
        tabContentType.find('.conversations-list').prepend(ele.get(0));
    }

    function messageTemplate(message, new_class = '') {
        let img = message.user.image ? '/'+message.user.image : message.user.avatar;
        return `<div class="message ${new_class}">
                    <a href="${window.location.href}/user/${message.user_id}/details" data-bs-toggle="modal" data-bs-target="#modal-user-profile" class="avatar avatar-responsive">
                        <img class="avatar-img" src="${img}" alt="" width='100%'>
                    </a>

                    <div class="message-inner">
                        <div class='layout-download d-none'></div>
                        <div class="message-body">
                            <div class="message-content">
                                <div class="${message.type == 'text' ? 'message-text' : ''}">
                                    ${buildFile (message.type, message.message)}
                                </div>
                            </div>
                        </div>

                        <div class="message-footer">
                            <span class="extra-small text-muted">${message.created_at}</span>
                        </div>
                    </div>
                </div>`;
    }

    function newMessagesTemplate(count) {
        return `<div class="message-divider"> <small class="text-muted">New ${count} Messages</small> </div>`;
    }


    function typing() {
        return `<div class="message user-typing">
                    <div class="message-inner">
                        <div class="message-body">
                            <div class="message-content">
                                <div class="message-text">
                                    <small class="text-truncate">is typing<span class="typing-dots"><span>.</span><span>.</span><span>.</span></span></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
    }

    function emptyChatTemplate() {
        return `<div class="container h-100" id='empty-chat'>
                    <div class="d-flex flex-column h-100 justify-content-center text-center">
                        <div class="mb-6">
                            <span class="icon icon-xl text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-message-square"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                            </span>
                        </div>

                        <p class="text-muted">No messages here yet..., <br> Send a message.</p>
                    </div>

                </div>`;
    }


    function toggleTyping(check, user_id)
    {
        let user_item = $('.conversations-list, .users-list').find(`[data-user-id="${user_id}"]`);
        if (user_item.length == 0) return;
        if (check) {
            user_item.find('.last-message').addClass('d-none');
            user_item.find('.user-typing').removeClass('d-none');
        } else {
            user_item.find('.last-message').removeClass('d-none');
            user_item.find('.user-typing').addClass('d-none');
        }
    }


    function toggleTypingInChat(check, user_id)
    {
        let ele = $('body').find(`[data-conversation-user="${user_id}"]`);
        if (ele.length == 0) return;
        if (check) {
            if (ele.find('.user-typing').length == 0)
                ele.append(typing());
        } else {
            ele.find('.user-typing').remove();
        }
    }

    function buildFile (type, src) {
        if (type == 'text/plain') return `<a href='${src}' target='_blank' class='btn btn-success'> ${type} </a>`;
        let text = '';
        let type_array = type.split('/');
        switch (type_array[0]) {
            case 'text':
                text = src;
            break;

            case 'image':
                text = `<img src='${src}' width='100%'> <div class='btn-download-chat-img d-none'> <a href='${src}' class='btn btn-sm btn-dark' download='${src.split('/').pop()}'>Download</a> </div>`;
            break;

            case 'audio':
                text = `<audio controls width='100%'> <source src="${src}"></audio>`;
            break;

            case 'video':
                text = `<video width="100%" controls> <source src="${src}"> </video>`;
            break;

            default:
                text = `<a href='${src}' target='_blank' class='btn btn-success'> ${type} </a>`;
                break;
        }

        return text;
    }

    function changeCounter(element_selector, step = 1, operator = '+')
    {
        ele = $('body').find(`${element_selector}`);
        counter = Number.parseInt(ele.first().text());
        if (step == 0 || (counter < step && operator == '-')) return;

        counter = eval(parseInt(counter) +`${operator}`+ Number.parseInt(step));
        ele.text(counter);
        ele.removeClass('d-none');
    }


    document.addEventListener('play', function (e) {
        $.each($('body').find('video, audio').not( e.target ), function (indexInArray, valueOfElement) {
            $(this).trigger('pause');
        });
    }, true);


    document.addEventListener('scroll', function (e) {
        let chat_window = $('body').find('#load-chat .hide-scrollbar');
        if ( chat_window.scrollTop() == 0 && next_messages_page !== null)
            loadMoreMessages($('#send-message').find('[name="conversation_id"]').val(), conversation_user_id);

        if (chat_window.scrollTop() + chat_window.innerHeight() >= chat_window[0].scrollHeight)
            chat_window.find('.message-divider').remove();

    }, true);

    $('#tab-friends').click();


    $('body').on('mouseenter', '#load-chat .message .message-content', function (e) {
        $(this).find('.btn-download-chat-img').removeClass('d-none');
    });

    $('body').on('mouseleave', '#load-chat .message .message-content', function (e) {
        $(this).find('.btn-download-chat-img').addClass('d-none');
    });

    function changeReadMessageIcon(user_id, status = 'send') {
        $(`[data-user-id="${user_id}"]`).find('.message-status-icons').addClass('d-none');

        if(status == 'receive') {
            $(`[data-user-id="${user_id}"]`).find('.receive-message-icon').removeClass('d-none');
        } else if(status == 'read') {
            $(`[data-user-id="${user_id}"]`).find('.read-message-icon').removeClass('d-none');
        } else if(status == 'send') {
            $(`[data-user-id="${user_id}"]`).find('.send-message-icon').removeClass('d-none');
        }
    }
});
