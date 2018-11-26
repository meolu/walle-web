import React from 'react';
import PropTypes from 'prop-types';
import {
    Modal,
    Form,
    Input,
} from 'antd';
const FormItem = Form.Item;

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
            label="角色名"
            hasFeedback
          >
            {getFieldDecorator('name', {
                rules: [{
                    required: true, message: '请输入角色名',
                }],
                initialValue: data.name,
            })(
              <Input placeholder="请输入角色名" />,
            )}
          </FormItem>
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
};

Panel.defaultProps = {
    data: {},
};

const RoleDetailsPanel = Form.create()(Panel);
RoleDetailsPanel.TYPE = TYPE;

export default RoleDetailsPanel;
