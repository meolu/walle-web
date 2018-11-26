const NProgress = require('nprogress');

module.exports = {
    path: 'list',

    getComponent(nextState, cb) {
        require.ensure([], (require) => {
            cb(null, require('components/page/RoleListPanel').default);
        }, 'roleListPanel');
    },

    onEnter: () => {
        NProgress.done();
    },

    onLeave: () => {
        NProgress.start();
    },
};
