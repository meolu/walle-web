import React from 'react';
import PropTypes from 'prop-types';
import {
    Modal,
    Form,
    Input,
    Select,
    Row,
    Col,
    Button,
} from 'antd';
const FormItem = Form.Item;
const Option = Select.Option;

const TYPE = {
    add: 'add',
    edit: 'edit',
};

const getTitle = (type) => {
    switch (type) {
        case TYPE.add:
            return '新增用户';
        case TYPE.edit:
            return '编辑用户';
        default:
            return '';
    }
};

const Panel = ({
    type,
    onSubmit,
    onCancel,
    form,
    data,
    roleList,
}) => {
    const title = getTitle(type);
    const { getFieldDecorator } = form;

    const formItemLayout = {
        labelCol: {
            xs: { span: 24 },
            sm: { span: 6 },
        },
        wrapperCol: {
            xs: { span: 24 },
            sm: { span: 14 },
        },
    };

    /**
     * 随机生成密码
     */
    const createRandomPassword = () => {
        const chars = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_@*!';
        let password = '';
        while (password.length < 35) {
            password += chars[Math.floor(Math.random() * chars.length)];
        }
        if (!/\d/.test(password)) {
            /**
             * 密码必须含有数字，如果不含有，随机选取一个字符，将其替换成一个随机数字
             */
            const letter = password[Math.floor(Math.random() * password.length)];
            password = password.replace(letter, Math.floor(Math.random() * 10));
        }
        if (!/[a-zA-Z]/.test(password)) {
            /**
             * 密码必须含有字母，如果不含有，随机选取一个数字，将其替换成一个随机字母
             */
            const number = password[Math.floor(Math.random() * password.length)];
            password = password.replace(number, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'[Math.floor(Math.random() * 52)]);
        }
        form.setFieldsValue({ password });
    };

    return (
      <Modal
        title={title}
        visible
        onOk={() => {
            form.validateFieldsAndScroll((err, values) => {
                if (!err) {
                    onSubmit(values);
                }
            });
        }}
        onCancel={onCancel}
      >
        <Form>
          {
              type === TYPE.edit ? (
                <FormItem
                  {...formItemLayout}
                  label="id"
                >
                  <span className="ant-form-text">{data.id}</span>
                </FormItem>
              ) : ''
          }
          <FormItem
            {...formItemLayout}
            label="用户名"
            hasFeedback
          >
            {getFieldDecorator('username', {
                rules: [{
                    required: true, message: '请输入用户名',
                }],
                initialValue: data.username,
            })(
              <Input placeholder="请输入用户名" />,
            )}
          </FormItem>
          <FormItem
            {...formItemLayout}
            label="密码"
          >
            <Row gutter={8}>
              <Col span={16}>
                {getFieldDecorator('password', {
                    rules: [{
                        required: type === TYPE.add,
                        message: '请输入密码',
                    }, {
                        min: 6,
                        max: 35,
                        message: '密码长度范围为 8-35',
                    }],
                })(
                  <Input placeholder="请输入密码" />,
                )}
              </Col>
              <Col span={8}>
                <Button
                  size="large"
                  onClick={createRandomPassword}
                >随机生成</Button>
              </Col>
            </Row>
          </FormItem>
          <FormItem
            {...formItemLayout}
            label="E-mail"
            hasFeedback
          >
            {getFieldDecorator('email', {
                rules: [{
                    type: 'email', message: '请输入合法的邮箱',
                }, {
                    required: true, message: '请输入邮箱',
                }],
                initialValue: data.email,
            })(
              <Input placeholder="请输入邮箱" disabled={type === TYPE.edit} />,
            )}
          </FormItem>
          <FormItem
            {...formItemLayout}
            label="角色"
            hasFeedback
          >
            {getFieldDecorator('role_id', {
                rules: [
                { required: true, message: '请选择角色' },
                ],
                initialValue: data.role_id ? `${data.role_id}` : undefined,
            })(
              <Select placeholder="请选择角色">
                {
                    roleList.map((role) => (
                      <Option key={role.id} value={`${role.id}`}>{role.role_name}</Option>
                    ))
                }
              </Select>,
            )}
          </FormItem>
          {
              type === TYPE.edit ? (
                <div>
                  <FormItem
                    {...formItemLayout}
                    label="创建时间"
                  >
                    <span className="ant-form-text">{data.created_at}</span>
                  </FormItem>
                  <FormItem
                    {...formItemLayout}
                    label="修改时间"
                  >
                    <span className="ant-form-text">{data.updated_at}</span>
                  </FormItem>
                </div>
              ) : ''
          }
        </Form>
      </Modal>
    );
};

Panel.propTypes = {
    type: PropTypes.string.isRequired,
    onSubmit: PropTypes.func.isRequired,
    onCancel: PropTypes.func.isRequired,
    form: PropTypes.shape().isRequired,
    data: PropTypes.shape(),
    roleList: PropTypes.arrayOf(PropTypes.object),
};

Panel.defaultProps = {
    data: {},
    roleList: [],
};

const UserDetailsPanel = Form.create()(Panel);
UserDetailsPanel.TYPE = TYPE;

export default UserDetailsPanel;
