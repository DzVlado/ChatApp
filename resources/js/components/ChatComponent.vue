<template>
    <div>
        <div class="card-body scroll" id="messages">
            <div v-for="message in this.messages" :key="message.id" class="alert alert-success"
                 :class="{ 'me': user.id == message.user_id }">
            <span class="name">
                <p v-if="user.id == message.user_id">Me:</p>
                <p v-else>{{ message.user.name }}:</p>
            </span>
                <p class="body">
                    {{ message.body }}
                </p>
            </div>
        </div>
        <div class="card-footer">
            <form @submit.prevent="sendMessage()">
                <div class="form-group">
                    <div class="row">
                        <textarea v-model="body" name="body" id="body" placeholder="Message body"
                                  class="form-control col-md-10" maxlength="191"></textarea>
                        <button class="btn btn-success col-md-2" type="submit">Send</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                user: {},
                messages: {},
                body: ''
            }
        },
        methods: {
            sendMessage() {
                if(this.body != ''){
                    axios.post('/messages', {
                        body: this.body,
                        user_id: user.id
                    });

                    this.body = '';

                    this.scrollToEnd();
                }
            },
            scrollToEnd: function() {
                $("#messages").animate({ scrollTop: $('#messages').prop("scrollHeight")}, 1000);
            }
        },
        created() {
            this.user = user;
            axios.get('/messages')
                .then(response => {
                    this.messages = response.data;
                });
        },
        mounted() {
            window.Echo.channel('message-sent')
                .listen('MessageSent', (e) => {
                    let newmessage = {};
                    newmessage.body = e.message.body;
                    newmessage.user_id = e.user.id;
                    newmessage.user = e.user;

                    this.messages.push(newmessage);

                    this.scrollToEnd();
                });
        }
    }
</script>
<style>
    .me{
        text-align: right;
        background-color: #fffbdb;
    }

    .name{
        font-size: 20px;
        font-weight: 600;
    }

    .body{
        font-size: 15px;
    }

    .scroll {
        max-height: 400px;
        overflow-y: auto;
    }
</style>