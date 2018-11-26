import React from 'react';
import PropTypes from 'prop-types';
import { Layout, Menu, Icon, Dropdown, Badge } from 'antd';
import style from './style.css';

const { Header } = Layout;

const WalleHeader = ({
    username,
}) => {
    const menu = (
      <Menu>
        <Menu.Item>
          <a // eslint-disable-line
            rel="noopener noreferrer" href="#"
          >退出</a>
        </Menu.Item>
      </Menu>
    );

    return (
      <Header className="header">
        <div className="logo">
          Walle 瓦力
        </div>
        <Menu
          theme="dark"
          mode="horizontal"
          style={{ lineHeight: '64px' }}
        >
          <Dropdown overlay={menu} trigger={['click']}>
            <a className={`${style.dropDown} ant-dropdown-link pull-right`}>
              {username} <Icon type="down" />
            </a>
          </Dropdown>
          <Menu.Item key="/message" style={{ float: 'right' }}>
            <Badge dot>
              <Icon type="message" />
            </Badge>
          </Menu.Item>
        </Menu>
      </Header>
    );
};

WalleHeader.propTypes = {
    username: PropTypes.string,
};

WalleHeader.defaultProps = {
    username: '登录',
};

export default WalleHeader;
