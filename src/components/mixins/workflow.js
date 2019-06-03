
export default {
    data(){
        return {
            dashboardPath: '/',
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
        onProcessInstance() {},
        onProcessCanceled() {},
        onTaskCompleted() {},
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
        completeTask(data, token = this.workflowToken) {
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
        gotoNextStep(token) {
            return this.processTasks(token).then(response => {
                const tasks = response.data.response;
                if (tasks.length === 1) {
                    this.openTask(tasks[0]);
                } else {
                    this.gotoDashboard();
                }
            });
        },
        validateToken(token) {
            const valid = token && token instanceof Object
                && token.instance
                && token.token;
            if (!valid) {
                throw "Invalid token: " + JSON.stringify(token);
            }
        }
    },
};
