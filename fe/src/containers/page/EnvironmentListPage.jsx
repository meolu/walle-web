import React, { Component } from 'react';
import {
    Table,
    Popconfirm,
    Button,
    message,
    Input,
} from 'antd';
import fetch from 'utils/fetch';

import EnvironmentDetailsPanel from '../panel/EnvironmentDetailsPanel';

const Search = Input.Search;

class EnvironmentListPage extends Component {

    constructor(...args) {
        super(...args);
        this.columns = [{
            title: 'id',
            dataIndex: 'id',
            key: 'id',
        }, {
            title: '环境名',
            dataIndex: 'env_name',
            key: 'env_name',
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
                      this.showEnvironmentDetailsPanel(EnvironmentDetailsPanel.TYPE.edit, record.id);
                  }}
                >编辑</a>
                <span className="ant-divider" />
                <Popconfirm
                  title={`确定删除${record.env_name}？`}
                  okText="确定"
                  cancelText="取消"
                  onConfirm={() => {
                      this.deleteEnvironment(record.id);
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
        this.fetchEnvironmentList();
    }

    /**
     * 获取环境列表
     * @memberof EnvironmentListPage
     */
    fetchEnvironmentList = (params = {}) => {
        this.setState({ loading: true });
        fetch({
            url: '/environment/',
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
     * @memberof EnvironmentListPage
     */
    handleTableChange = (pagination) => {
        const pager = { ...this.state.pagination };
        pager.current = pagination.current;
        this.setState({
            pagination: pager,
        });
        this.fetchEnvironmentList({
            page: pagination.current,
        });
    }

    /**
     * 添加环境
     * @memberof EnvironmentListPage
     */
    createEnvironment = (env) => {
        const hide = message.loading('正在处理...', 0);
        fetch({
            url: '/environment/',
            method: 'post',
            data: env,
        }).then(() => {
            hide();
            this.hideEnvironmentDetailsPanel();
            this.fetchEnvironmentList();
        }).catch(hide);
    }

    /**
     * 更新环境
     * @memberof EnvironmentListPage
     */
    updateEnvironment = (id, env) => {
        const hide = message.loading('正在处理...', 0);
        fetch({
            url: `/environment/${id}`,
            method: 'put',
            data: env,
        }).then(() => {
            hide();
            message.success('已更新');
            this.hideEnvironmentDetailsPanel();
            this.fetchEnvironmentList();
        }).catch(hide);
    }

    /**
     * 删除环境
     * @memberof EnvironmentListPage
     */
    deleteEnvironment = (id) => {
        const hide = message.loading('正在处理...', 0);
        fetch({
            url: `/environment/${id}`,
            method: 'delete',
        }).then(() => {
            hide();
            message.success('已删除');
            this.fetchEnvironmentList();
        }).catch(hide);
    }

    /**
     * 展示环境面板
     * @memberof EnvironmentListPage
     */
    showEnvironmentDetailsPanel = (type, id) => {
        this.setState({
            detailsPanel: {
                visible: true,
                type,
                onSubmit: (env) => {
                    this.updateEnvironment(id, env);
                },
                onCancel: this.hideEnvironmentDetailsPanel,
                data: {},
            },
        });
    }

    /**
     * 隐藏环境面板
     * @memberof EnvironmentListPage
     */
    hideEnvironmentDetailsPanel = () => {
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
                    this.showEnvironmentDetailsPanel(EnvironmentDetailsPanel.TYPE.add);
                }}
              >添加环境</Button>
              <Search
                placeholder="搜索环境"
                style={{ width: 200, marginLeft: 20 }}
                onSearch={value => this.fetchEnvironmentList({ kw: value })}
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
                  <EnvironmentDetailsPanel {...detailsPanel} />
                ) : ''
            }
          </div>
        );
    }
}

export default EnvironmentListPage;
