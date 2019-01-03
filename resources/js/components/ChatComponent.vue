<template>
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Frends</div>
                <div class="card-body scroll" id="frends">
                    <span class="name">
                        <p class="btn btn-primary col-md-12" @click="getGroupMessages()">Group Chat</p>
                    </span>
                    <span class="name" v-for="frend in this.frends" :key="frend.id">
                        <p class="btn btn-info col-md-12" @click="getUserMessages(frend)">{{ frend.name }}</p>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ this.header }}</div>
                <div class="card-body scroll" id="messages">
                    <div v-for="message in this.messages" :key="message.id" class="alert alert-success" :class="{ 'me': user.id == message.user_id }">
                    <span class="name">
                        <p v-if="user.id == message.user_id">Me:</p>
                        <p v-else>{{ message.name }}:</p>
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
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                user: {},
                toUser:{},
                header: '',
                messages: {},
                body: '',
                frends: {}
            }
        },
        methods: {
            sendMessage() {
                if(this.body != ''){
                    axios.post('/messages', {
                        body: this.body,
                        user_id: user.id,
                        frend: this.toUser.id
                    });

                    if(this.toUser.id != null){
                        this.createNewMessage(this.body, this.user.id, this.user.name);
                    }

                    this.body = '';

                    this.scrollToEnd();
                }
            },
            scrollToEnd: function() {
                $("#messages").animate({ scrollTop: $('#messages').prop("scrollHeight")}, 1000);
            },
            getGroupMessages(){
                this.header = 'Group Chat';
                this.toUser = {};
                axios.get('/messages')
                    .then(response => {
                        this.messages = response.data;
                    });
            },
            getFrends(){
                axios.get('/users')
                    .then(response => {
                        this.frends = response.data;
                    });
            },
            getUserMessages(frend){
                this.toUser = frend;
                axios.get('/messages',{
                    params: {
                        user: this.user.id,
                        frend: frend.id
                    }
                })
                    .then(response => {
                        this.header = frend.name;
                        this.messages = response.data;
                    });
            },
            createNewMessage(body, user_id, user_name){
                let newmessage = {};
                newmessage.body = body;
                newmessage.user_id = user_id;
                newmessage.name = user_name;

                this.messages.push(newmessage);

                this.scrollToEnd();
            }
        },
        created() {
            this.user = user;
            this.getGroupMessages();
            this.getFrends();
        },
        mounted() {
            window.Echo.channel('message-sent')
                .listen('MessageSent', (e) => {
                    this.createNewMessage(e.message.body, e.user.id, e.user.name);
                });

            window.Echo.private('user-' + this.user.id)
                .listen('PrivateMessageSent', (e) => {
                    if(e.user.id == this.toUser.id){
                        this.createNewMessage(e.message.body, e.user.id, e.user.name);
                    }
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