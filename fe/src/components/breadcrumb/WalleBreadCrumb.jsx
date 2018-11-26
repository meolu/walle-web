import React from 'react';
import PropTypes from 'prop-types';
import {
  Link,
} from 'react-router-dom';
import { Breadcrumb } from 'antd';

const WalleBreadCrumb = ({
    location,
}) => {
    let pathname = location.pathname;
    let breads = pathname === '/' ? [] : ['/'];

    while (pathname) {
        breads.unshift(pathname);
        pathname = pathname.replace(/\/[\w]*$/, '');
    }
    breads = breads.reverse();

    return (
      <Breadcrumb style={{ margin: '12px 0' }}>
        {
            breads.map((item, index) => {
                const lastPath = item.match(/\/([\w]+)$/);
                const text = lastPath ? lastPath[1] : 'Home';
                return (
                  <Breadcrumb.Item key={item}>
                    {
                        index === breads.length - 1 ? text : (
                          <Link to={item}>{text}</Link>
                        )
                    }
                  </Breadcrumb.Item>
                );
            })
        }
      </Breadcrumb>
    );
};

WalleBreadCrumb.propTypes = {
    location: PropTypes.shape().isRequired,
};

export default WalleBreadCrumb;
