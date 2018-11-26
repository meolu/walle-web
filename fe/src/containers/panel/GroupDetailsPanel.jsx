import React, { Component } from 'react';
import PropTypes from 'prop-types';
import {
    Modal,
    Form,
    Row,
    Col,
    Input,
    Select,
    Spin,
    Icon,
} from 'antd';
import fetch from 'utils/fetch';
import debounce from 'lodash.debounce';

const FormItem = Form.Item;
const Option = Select.Option;

const TYPE = {
    add: 'add',
    edit: 'edit',
};

const getTitle = (type) => {
    switch (type) {
        case TYPE.add:
            return '新增用户组';
        case TYPE.edit:
            return '编辑用户组';
        default:
            return '';
    }
};

class Panel extends Component {

    static propTypes = {
        type: PropTypes.string.isRequired,
        onCancel: PropTypes.func.isRequired,
    };

    static defaultProps = {
        data: {},
        roleList: [],
    };

    static TYPE = TYPE;

    constructor(props) {
        super(props);
        this.fetchUser = debounce(this.fetchUser, 800);
    }

    state = {
        searchKw: [],
        searchResults: [],
        searchFetching: false,
        users: [
            {
                id: 1,
                username: '用户一',
            }, {
                id: 2,
                username: '用户二',
            },
        ],
    };

    fetchUser = (kw) => {
        this.setState({ searchFetching: true });
        fetch({
            url: '/user/',
            data: {
                kw,
            },
        }).then(resp => {
            const { data: { list } } = resp;
            this.setState({
                searchResults: list,
                searchFetching: false,
            });
        });
    }

    handleSearchChange = (searchKw) => {
        this.setState({
            searchKw,
            searchResults: [],
            searchFetching: false,
        });
    }

    addUser = ({ key, label }) => {
        this.setState({
            users: [
                ...this.state.users,
                {
                    id: key,
                    username: label,
                },
            ],
        });
    }

    render() {
        const { type, onCancel } = this.props;
        const { searchFetching, searchResults, searchKw, users } = this.state;

        const title = getTitle(type);

        const formItemLayout = {
            labelCol: { span: 5 },
            wrapperCol: { span: 19 },
        };
        return (
          <Modal
            title={title}
            visible
            onOk={() => {

            }}
            onCancel={onCancel}
          >
            <Form
              className="ant-advanced-search-form"
            >
              <Row>
                <Col span={12}>
                  <FormItem {...formItemLayout} label="用户组">
                    <Input placeholder="用户组名称" />
                  </FormItem>
                </Col>
                <Col span={11} offset={1}>
                  <FormItem {...formItemLayout}>
                    <Select
                      mode="multiple"
                      labelInValue
                      value={searchKw}
                      placeholder="搜索用户"
                      notFoundContent={searchFetching ? <Spin size="small" /> : null}
                      filterOption={false}
                      onSearch={this.fetchUser}
                      onChange={this.handleSearchChange}
                      onSelect={this.addUser}
                      style={{ width: '100%' }}
                    >
                      {searchResults.map(d => <Option key={d.id}>{d.username}</Option>)}
                    </Select>
                  </FormItem>
                </Col>
              </Row>
              <Row className="user-group-wrapper">
                {
                  users.map((user, index) => (
                    <Col
                      key={user.id}
                      span={8}
                    >
                      <i className="user-icon" />
                      <p className="user-name">{user.username}</p>
                      <a // eslint-disable-line
                        className="delete-btn"
                        onClick={() => {
                            console.log(users.slice(0, index));
                            console.log(users.slice(index));
                            this.setState({
                                users: [
                                    ...users.slice(0, index),
                                    ...users.slice(index + 1),
                                ],
                            })
                        }}
                      >
                        <Icon type="minus-circle-o" style={{ color: 'red' }} />
                      </a>
                    </Col>
                  ))
                }
              </Row>
            </Form>
          </Modal>
        );
    }
}

export default Panel;
