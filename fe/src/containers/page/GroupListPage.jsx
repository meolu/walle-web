import React, { Component } from 'react';
import {
    Table,
    Popconfirm,
    Button,
    message,
    Input,
} from 'antd';
import fetch from 'utils/fetch';

import GroupDetailsPanel from '../panel/GroupDetailsPanel';

const Search = Input.Search;

class GroupListPage extends Component {

    constructor(...args) {
        super(...args);
        this.columns = [{
            title: 'id',
            dataIndex: 'id',
            key: 'id',
        }, {
            title: '用户组名',
            dataIndex: 'name',
            key: 'name',
        }, {
            title: '用户数',
            dataIndex: 'users',
            key: 'users',
        }, {
            title: '操作',
            key: 'action',
            render: (text, record) => (
              <span>
                <a // eslint-disable-line
                  onClick={() => {
                      this.showGroupDetailsPanel(GroupDetailsPanel.TYPE.edit, record.id);
                  }}
                >编辑</a>
                <span className="ant-divider" />
                <Popconfirm
                  title={`确定删除${record.name}？`}
                  okText="确定"
                  cancelText="取消"
                  onConfirm={() => {
                      this.deleteGroup(record.id);
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
        this.fetchGroupList();
    }

    /**
     * 获取用户组列表
     * @memberof GroupListPage
     */
    fetchGroupList = (params = {}) => {
        this.setState({ loading: true });
        fetch({
            url: '/group/',
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
     * @memberof GroupListPage
     */
    handleTableChange = (pagination) => {
        const pager = { ...this.state.pagination };
        pager.current = pagination.current;
        this.setState({
            pagination: pager,
        });
        this.fetchGroupList({
            page: pagination.current,
        });
    }

    /**
     * 添加用户组
     * @memberof GroupListPage
     */
    createGroup = (group) => {
        const hide = message.loading('正在处理...', 0);
        fetch({
            url: '/group/',
            method: 'post',
            data: group,
        }).then(() => {
            hide();
            this.hideGroupDetailsPanel();
            this.fetchGroupList();
        }).catch(hide);
    }

    /**
     * 更新用户组
     * @memberof GroupListPage
     */
    updateGroup = (id, group) => {
        const hide = message.loading('正在处理...', 0);
        fetch({
            url: `/group/${id}`,
            method: 'put',
            data: group,
        }).then(() => {
            hide();
            message.success('已更新');
            this.hideGroupDetailsPanel();
            this.fetchGroupList();
        }).catch(hide);
    }

    /**
     * 删除用户组
     * @memberof GroupListPage
     */
    deleteGroup = (id, group) => {
        const hide = message.loading('正在处理...', 0);
        fetch({
            url: `/group/${id}`,
            method: 'delete',
            data: group,
        }).then(() => {
            hide();
            message.success('已删除');
            this.fetchGroupList();
        }).catch(hide);
    }

    /**
     * 展示用户组面板
     * @memberof GroupListPage
     */
    showGroupDetailsPanel = (type, id) => {
        if (type === GroupDetailsPanel.TYPE.add) {
            this.setState({
                detailsPanel: {
                    visible: true,
                    type,
                    onSubmit: (group) => {
                        this.createGroup(id, group);
                    },
                    onCancel: this.hideGroupDetailsPanel,
                },
            });
        } else {
            const hide = message.loading('处理中...', 0);
            fetch({ url: `/group/${id}` }).then(resp => {
                hide();
                this.setState({
                    detailsPanel: {
                        visible: true,
                        type,
                        onSubmit: (group) => {
                            this.updateGroup(id, group);
                        },
                        onCancel: this.hideGroupDetailsPanel,
                        data: resp.data,
                    },
                });
            }).catch(hide);
        }
    }

    /**
     * 隐藏用户组面板
     * @memberof GroupListPage
     */
    hideGroupDetailsPanel = () => {
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
                    this.showGroupDetailsPanel(GroupDetailsPanel.TYPE.add);
                }}
              >添加用户组</Button>
              <Search
                placeholder="输入用户组名或邮箱"
                style={{ width: 200, marginLeft: 20 }}
                onSearch={value => this.fetchGroupList({ kw: value })}
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
                  <GroupDetailsPanel {...detailsPanel} />
                ) : ''
            }
          </div>
        );
    }
}

export default GroupListPage;
