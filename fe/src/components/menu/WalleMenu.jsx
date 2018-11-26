import React from 'react';
import PropTypes from 'prop-types';
import { Menu, Icon } from 'antd';
import { Link } from 'react-router-dom';
const { SubMenu } = Menu;

const WalleMenu = ({
  location,
}) => {
    const pathname = location.pathname;
    return (
      <Menu
        mode="inline"
        defaultOpenKeys={['config', 'project']}
        defaultSelectedKeys={[pathname]}
        style={{ height: '100%' }}
      >
        <SubMenu key="config" title={<span><Icon type="user" />用户中心</span>}>
          <Menu.Item key="/user">
            <Link to="/user">用户列表</Link>
          </Menu.Item>
          <Menu.Item key="/group">
            <Link to="/group">用户组列表</Link>
          </Menu.Item>
          <Menu.Item key="/role">
            <Link to="/role">角色列表</Link>
          </Menu.Item>
        </SubMenu>
        <SubMenu key="project" title={<span><Icon type="laptop" />配置中心</span>}>
          <Menu.Item key="/environment">
            <Link to="/environment">环境管理</Link>
          </Menu.Item>
        </SubMenu>
      </Menu>
    );
};

WalleMenu.propTypes = {
    location: PropTypes.shape().isRequired,
};

export default WalleMenu;
