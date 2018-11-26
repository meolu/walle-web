const NProgress = require('nprogress');

module.exports = {
    path: 'list',

    getComponent(nextState, cb) {
        require.ensure([], (require) => {
            cb(null, require('components/page/UserListPanel').default);
        }, 'userListPanel');
    },

    onEnter: () => {
        NProgress.done();
    },

    onLeave: () => {
        NProgress.start();
    },
};
