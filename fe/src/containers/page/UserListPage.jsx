import React, { Component } from 'react';
import {
    Table,
    Popconfirm,
    Button,
    message,
    Input,
} from 'antd';
import fetch from 'utils/fetch';

import UserDetailsPanel from '../panel/UserDetailsPanel';

const Search = Input.Search;

class UserListPage extends Component {

    constructor(...args) {
        super(...args);
        this.columns = [{
            title: 'id',
            dataIndex: 'id',
            key: 'id',
        }, {
            title: '用户名',
            dataIndex: 'username',
            key: 'username',
        }, {
            title: '角色',
            dataIndex: 'role_name',
            key: 'role_name',
        }, {
            title: '邮箱',
            dataIndex: 'email',
            key: 'email',
        }, {
            title: '状态',
            dataIndex: 'status',
            key: 'status',
        }, {
            title: '操作',
            key: 'action',
            render: (text, record) => (
              <span>
                <a // eslint-disable-line
                  onClick={() => {
                      this.showUserDetailsPanel(UserDetailsPanel.TYPE.edit, record.id);
                  }}
                >编辑</a>
                <span className="ant-divider" />
                <a // eslint-disable-line
                  onClick={() => {
                      this.changeUserStatus(record.id, record.status);
                  }}
                >{record.status === 0 ? '冻结' : '解冻'}</a>
                <span className="ant-divider" />
                <Popconfirm
                  title={`确定删除${record.username}？`}
                  okText="确定"
                  cancelText="取消"
                  onConfirm={() => {
                      this.deleteUser(record.id);
                  }}
                >
                  <a // eslint-disable-line
                    href="#"
                  >删除</a>
                </Popconfirm>
              </span>
            ),
        }];
    }

    state = {
        data: [],
        loading: false,
        pagination: {},
        detailsPanel: {
            visible: false,
        },
    }

    componentWillMount() {
        this.fetchUserList();
    }

    /**
     * 获取用户列表
     * @memberof UserListPage
     */
    fetchUserList = (params = {}) => {
        this.setState({ loading: true });
        fetch({
            url: '/user/',
            data: {
                ...params,
                size: 10,
            },
        }).then(resp => {
            const { data: { count, list } } = resp;
            this.setState({
                data: list,
                loading: false,
                pagination: {
                    total: count,
                },
            });
        });
    }

    /**
     * 表格分页
     * @memberof UserListPage
     */
    handleTableChange = (pagination) => {
        const pager = { ...this.state.pagination };
        pager.current = pagination.current;
        this.setState({
            pagination: pager,
        });
        this.fetchUserList({
            page: pagination.current,
        });
    }

    /**
     * 添加用户
     * @memberof UserListPage
     */
    createUser = (user) => {
        const hide = message.loading('正在处理...', 0);
        fetch({
            url: '/user/',
            method: 'post',
            data: user,
        }).then(() => {
            hide();
            this.hideUserDetailsPanel();
            this.fetchUserList();
        }).catch(hide);
    }

    /**
     * 更新用户
     * @memberof UserListPage
     */
    updateUser = (id, user) => {
        const hide = message.loading('正在处理...', 0);
        fetch({
            url: `/user/${id}`,
            method: 'put',
            data: user,
        }).then(() => {
            hide();
            message.success('已更新');
            this.hideUserDetailsPanel();
            this.fetchUserList();
        }).catch(hide);
    }

    /**
     * 删除用户
     * @memberof UserListPage
     */
    deleteUser = (id, user) => {
        const hide = message.loading('正在处理...', 0);
        fetch({
            url: `/user/${id}`,
            method: 'delete',
            data: user,
        }).then(() => {
            hide();
            message.success('已删除');
            this.fetchUserList();
        }).catch(hide);
    }

    /**
     * 展示用户面板
     * @memberof UserListPage
     */
    showUserDetailsPanel = (type, id) => {
        const hide = message.loading('处理中...', 0);
        const roleFetch = fetch({ url: '/role/' });

        if (type === UserDetailsPanel.TYPE.edit) {
            const fetchs = [
                fetch({ url: `/user/${id}` }),
                roleFetch,
            ];

            Promise.all(fetchs).then(results => {
                hide();
                const [userResp, roleResp] = results;
                this.setState({
                    detailsPanel: {
                        visible: true,
                        type,
                        onSubmit: (user) => {
                            this.updateUser(id, user);
                        },
                        onCancel: this.hideUserDetailsPanel,
                        data: userResp.data,
                        roleList: roleResp.data.list,
                    },
                });
            }).catch(hide);
        } else {
            roleFetch.then(resp => {
                hide();
                this.setState({
                    detailsPanel: {
                        visible: true,
                        type,
                        onSubmit: (user) => {
                            this.createUser(user);
                        },
                        onCancel: this.hideUserDetailsPanel,
                        roleList: resp.data.list,
                    },
                });
            }).catch(hide);
        }
    }

    /**
     * 隐藏用户面板
     * @memberof UserListPage
     */
    hideUserDetailsPanel = () => {
        this.setState({
            detailsPanel: {
                visible: false,
            },
        });
    }

    render() {
        const { data, pagination, loading, detailsPanel } = this.state;
        return (
          <div>
            <div>
              <Button
                type="primary"
                onClick={() => {
                    this.showUserDetailsPanel(UserDetailsPanel.TYPE.add);
                }}
              >添加用户</Button>
              <Search
                placeholder="输入用户名或邮箱搜索"
                style={{ width: 200, marginLeft: 20 }}
                onSearch={value => this.fetchUserList({ kw: value })}
              />
            </div>
            <br />
            <div>
              <Table
                columns={this.columns}
                rowKey={record => record.id}
                dataSource={data}
                pagination={pagination}
                loading={loading}
                onChange={this.handleTableChange}
              />
            </div>
            {
                detailsPanel.visible ? (
                  <UserDetailsPanel {...detailsPanel} />
                ) : ''
            }
          </div>
        );
    }
}

export default UserListPage;
