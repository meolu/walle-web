import React from 'react';
import {
  Route,
  Switch,
} from 'react-router-dom';
import { Layout } from 'antd';
import WalleHeader from 'components/header/WalleHeader';
import WalleMenu from 'components/menu/WalleMenu';
import WalleBreadCrumb from 'components/breadcrumb/WalleBreadCrumb';
import HomePage from 'components/page/HomePage';
import UserListPage from 'containers/page/UserListPage';
import RoleListPage from 'containers/page/RoleListPage';
import GroupListPage from 'containers/page/GroupListPage';
import EnvironmentListPage from 'containers/page/EnvironmentListPage';
import NotFountPage from 'components/page/NotFountPage';
import 'styles/App.css';

const { Content, Sider } = Layout;

const App = () => (
  <Layout>
    <WalleHeader />
    <Layout>
      <Sider width={200} style={{ background: '#fff' }}>
        <Route path="/" component={WalleMenu} />
      </Sider>
      <Layout style={{ padding: '0 24px 24px' }}>
        <Route path="/" component={WalleBreadCrumb} />
        <Content style={{ background: '#fff', padding: 24, margin: 0, minHeight: 280 }}>
          <Switch>
            <Route exact path="/" component={HomePage} />
            <Route path="/user" component={UserListPage} />
            <Route path="/role" component={RoleListPage} />
            <Route path="/group" component={GroupListPage} />
            <Route path="/environment" component={EnvironmentListPage} />
            <Route component={NotFountPage} />
          </Switch>
        </Content>
      </Layout>
    </Layout>
  </Layout>
);

export default App;
