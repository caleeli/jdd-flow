
export default {
    data() {
        return {
            dashboardPath: '/',
            nextStepPath: '/process/next',
            socketListeners: [],
        };
    },
    computed: {
        workflowToken() {
            return {
                instance: this.$route.query.instance,
                token: this.$route.query.token,
            };
        }
    },
    methods: {
        onProcessInstance() { },
        onProcessCanceled() { },
        onTaskCompleted() { },
        callProcess(processUrl, data = {}) {
            return window.axios.post('process', {
                call: {
                    method: 'call',
                    parameters: {
                        processUrl: processUrl,
                        data: data,
                    },
                }
            }).then((response) => {
                const instance = response.data.response;
                this.onProcessInstance(instance);
                this.gotoNextStep({
                    instance: instance.id,
                    token: null,
                });
                return response;
            });
        },
        startProcess(processUrl, start, data = {}) {
            return window.axios.post('process', {
                call: {
                    method: 'start',
                    parameters: {
                        processUrl: processUrl,
                        start: start,
                        data: data,
                    },
                }
            }).then((response) => {
                const instance = response.data.response;
                this.onProcessInstance(instance);
                this.gotoNextStep({
                    instance: instance.id,
                    token: null,
                });
                return response;
            });
        },
        completeTask(data = {}, token = this.workflowToken) {
            this.validateToken(token);
            return window.axios.post('process/' + token.instance, {
                call: {
                    method: 'completeTask',
                    parameters: {
                        token: token.token,
                        data: data,
                    },
                }
            }).then((response) => {
                this.onTaskCompleted(token);
                this.gotoNextStep(token);
                return response;
            });
        },
        cancelProcess(token = this.workflowToken) {
            this.validateToken(token);
            return window.axios.post('process/' + token.instance, {
                call: {
                    method: 'cancel',
                    parameters: {},
                }
            }).then((response) => {
                this.onProcessCanceled(token);
                this.gotoDashboard();
                return response;
            });
        },
        processTasks(token) {
            return window.axios.post('process/' + token.instance, {
                call: {
                    method: 'tasks',
                    parameters: {
                    },
                }
            });
        },
        openTask(task) {
            this.$router.push({
                path: task.path,
                query: task.token
            });
        },
        gotoDashboard() {
            this.$router.push({
                path: this.dashboardPath,
            });
        },
        showTasks(token, tasks = []) {
            this.$router.push({
                path: this.tasksPath,
                query: token,
                props: {
                    token,
                    tasks
                }
            });
        },
        gotoNextStep(token) {
            this.$router.push({
                path: this.nextStepPath,
                query: token
            });
        },
        validateToken(token) {
            const valid = token && token instanceof Object
                && token.instance
                && token.token;
            if (!valid) {
                throw "Invalid token: " + JSON.stringify(token);
            }
        },
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
        listenConsole(callback, instance = this.$route.query.instance, token = this.$route.query.token) {
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
        processData(variable, value) {
            const instance = this.$route.query.instance;
            this.$api.process.load(instance).then(process => {
                const data = process.attributes.data;
                if (data[variable]) {
                    value = value instanceof Object && !(value instanceof Array)
                        && data[variable] instanceof Object && !(data[variable] instanceof Array)
                        ? Object.assign(value, data[variable]) : data[variable];
                }
            });
            return value;
        },
    },
    destroyed: function () {
        this.cleanSocketListeners();
    },
};
