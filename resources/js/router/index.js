import { createRouter, createWebHashHistory } from 'vue-router';

import OrganizationsView from '@/views/OrganizationsView.vue';
import OrganizationView from '@/views/OrganizationView.vue';
import AuthView from '@/views/AuthView.vue';
import { getAccessToken, removeAccessToken } from '@/lib/auth-token';
import { queryClient } from '@/lib/query-client';
import { currentUserQueryKey, currentUserQueryOptions } from '@/queries/auth';

const router = createRouter({
  history: createWebHashHistory(),
  routes: [
    {
      path: '/',
      name: 'auth',
      component: AuthView,
      meta: {
        guestOnly: true,
      },
    },
    {
      path: '/organizations',
      name: 'organizations.index',
      component: OrganizationsView,
      meta: {
        requiresAuth: true,
      },
    },
    {
      path: '/organizations/:id',
      name: 'organizations.show',
      component: OrganizationView,
      meta: {
        requiresAuth: true,
      },
    },
  ],
});

router.beforeEach(async (to) => {
  const accessToken = getAccessToken();

  if (!accessToken) {
    queryClient.removeQueries({ queryKey: currentUserQueryKey });

    return to.meta.requiresAuth ? { name: 'auth' } : true;
  }

  try {
    await queryClient.query(currentUserQueryOptions);
  } catch {
    removeAccessToken();
    queryClient.removeQueries({ queryKey: currentUserQueryKey });

    return { name: 'auth' };
  }

  if (to.meta.guestOnly) {
    return { name: 'organizations.index' };
  }

  return true;
});

export default router;
