import axios from 'axios';

import { getAccessToken, removeAccessToken } from '@/lib/auth-token';

const http = axios.create({
  baseURL: '/api',
  headers: {
    Accept: 'application/json',
  },
});

http.interceptors.request.use((config) => {
  const accessToken = getAccessToken();

  if (accessToken) {
    config.headers.Authorization = `Bearer ${accessToken}`;
  }

  return config;
});

http.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      removeAccessToken();

      if (window.location.hash !== '#/') {
        window.location.replace('/#/');
      }
    }

    return Promise.reject(error);
  },
);

export default http;
