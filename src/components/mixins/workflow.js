
export default {
    data() {
        return {
            socketListeners: [],
            bpmn: {
                // Events
                NewProcess: null,
                // Methods
                tokens: this.$api.process_tokens,
                call: (process, data = {}) => {
                    return this.$api.process.call('call', { process, data });
                },
                complete: (token, data) => {
                    return this.$api.process[token.attributes.process_id].call('complete', { tokenId: token.id, data });
                },
                rowCall: (process, data = {}) => {
                    return this.$api.process.rowCall('call', { process, data });
                },
                rowComplete: (token, data) => {
                    return this.$api.process[token.attributes.process_id].rowCall('complete', { tokenId: token.id, data });
                },
            },
        };
    },
    methods: {
        addSocketListener(channel, event, callback) {
            this.socketListeners.push({
                channel,
                event,
            });
            window.Echo.private(channel).listen(
                event,
                callback,
            );
        },
        listenBpmn(callback, instance = this.$route.query.instance, token = this.$route.query.token) {
            const channel = `Process.${instance}.Token.${token}`;
            this.addSocketListener(channel, ".ElementConsole", callback);
        },
        cleanSocketListeners() {
            // Stop registered socket listeners 
            this.socketListeners.forEach(element => {
                window.Echo.private(
                    element.channel
                ).stopListening(element.event);
            });
        },
    },
    mounted() {
        this.addSocketListener('Bpmn', '.NewProcess', (data) => {
            this.bpmn.NewProcess = data;
        });
    },
};
