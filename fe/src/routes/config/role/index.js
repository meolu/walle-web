module.exports = {
    path: 'role',

    getChildRoutes(partialNextState, cb) {
        require.ensure([], (require) => {
            cb(null, [
                require('./add'),
                require('./edit'),
                require('./list'),
            ]);
        });
    },
};
