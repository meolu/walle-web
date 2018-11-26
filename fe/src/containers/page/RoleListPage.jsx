import React, { Component } from 'react';
import {
    Table,
    Popconfirm,
    Button,
    message,
    Input,
} from 'antd';
import fetch from 'utils/fetch';

import RoleDetailsPanel from '../panel/RoleDetailsPanel';

const Search = Input.Search;

class RoleListPage extends Component {

    constructor(...args) {
        super(...args);
        this.columns = [{
            title: 'id',
            dataIndex: 'id',
            key: 'id',
        }, {
            title: '角色名',
            dataIndex: 'role_name',
            key: 'role_name',
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
                      this.showRoleDetailsPanel(RoleDetailsPanel.TYPE.edit, record.id);
                  }}
                >编辑</a>
                <span className="ant-divider" />
                <Popconfirm
                  title={`确定删除${record.role_name}？`}
                  okText="确定"
                  cancelText="取消"
                  onConfirm={() => {
                      this.deleteRole(record.id);
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
        this.fetchRoleList();
    }

    /**
     * 获取角色列表
     * @memberof RoleListPage
     */
    fetchRoleList = (params = {}) => {
        this.setState({ loading: true });
        fetch({
            url: '/role/',
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
     * @memberof RoleListPage
     */
    handleTableChange = (pagination) => {
        const pager = { ...this.state.pagination };
        pager.current = pagination.current;
        this.setState({
            pagination: pager,
        });
        this.fetchRoleList({
            page: pagination.current,
        });
    }

    /**
     * 添加角色
     * @memberof RoleListPage
     */
    createRole = (role) => {
        const hide = message.loading('正在处理...', 0);
        fetch({
            url: '/role/',
            method: 'post',
            data: role,
        }).then(() => {
            hide();
            this.hideRoleDetailsPanel();
            this.fetchRoleList();
        }).catch(hide);
    }

    /**
     * 更新角色
     * @memberof RoleListPage
     */
    updateRole = (id, role) => {
        const hide = message.loading('正在处理...', 0);
        fetch({
            url: `/role/${id}`,
            method: 'put',
            data: role,
        }).then(() => {
            hide();
            message.success('已更新');
            this.hideRoleDetailsPanel();
            this.fetchRoleList();
        }).catch(hide);
    }

    /**
     * 删除角色
     * @memberof RoleListPage
     */
    deleteRole = (id) => {
        const hide = message.loading('正在处理...', 0);
        fetch({
            url: `/role/${id}`,
            method: 'delete',
        }).then(() => {
            hide();
            message.success('已删除');
            this.fetchRoleList();
        }).catch(hide);
    }

    /**
     * 展示角色面板
     * @memberof RoleListPage
     */
    showRoleDetailsPanel = (type, id) => {
        this.setState({
            detailsPanel: {
                visible: true,
                type,
                onSubmit: (role) => {
                    this.updateRole(id, role);
                },
                onCancel: this.hideRoleDetailsPanel,
                data: {},
            },
        });
    }

    /**
     * 隐藏角色面板
     * @memberof RoleListPage
     */
    hideRoleDetailsPanel = () => {
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
                    this.showRoleDetailsPanel(RoleDetailsPanel.TYPE.add);
                }}
              >添加角色</Button>
              <Search
                placeholder="搜索角色"
                style={{ width: 200, marginLeft: 20 }}
                onSearch={value => this.fetchRoleList({ kw: value })}
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
                  <RoleDetailsPanel {...detailsPanel} />
                ) : ''
            }
          </div>
        );
    }
}

export default RoleListPage;
