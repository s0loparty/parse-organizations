import { queryOptions } from '@tanstack/vue-query';

import http from '@/lib/http';

export const currentUserQueryKey = ['auth', 'user'];

export const currentUserQueryOptions = queryOptions({
  queryKey: currentUserQueryKey,
  queryFn: async () => {
    const response = await http.get('/user');

    return response.data;
  },
  staleTime: 'static',
});

export async function login(credentials) {
  const response = await http.post('/login', credentials);

  return response.data;
}
