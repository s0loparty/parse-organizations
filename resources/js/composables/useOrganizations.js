import { computed, ref } from 'vue';
import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';

import http from '@/lib/http';

const organizationsQueryKey = ['organizations'];

export function useOrganizations() {
  const queryClient = useQueryClient();
  const isCreateFormOpen = ref(false);
  const organizationUrl = ref('');

  const organizationsQuery = useQuery({
    queryKey: organizationsQueryKey,
    queryFn: async () => {
      const response = await http.get('/v1/organizations');

      return response.data.data;
    },
    refetchInterval: (query) => {
      const hasPendingOrganization = query.state.data?.some(
        (organization) => organization.status === 'pending',
      );

      return hasPendingOrganization ? 5_000 : false;
    },
  });

  const createOrganizationMutation = useMutation({
    mutationFn: async (url) => {
      const response = await http.post('/v1/organizations', { url });

      return response.data.data;
    },
    onSuccess: (organization) => {
      queryClient.setQueryData(organizationsQueryKey, (organizations = []) => {
        const exists = organizations.some(
          (item) => item.id === organization.id,
        );

        if (!exists) {
          return [organization, ...organizations];
        }

        return organizations.map((item) =>
          item.id === organization.id ? organization : item,
        );
      });

      organizationUrl.value = '';
      isCreateFormOpen.value = false;
    },
  });

  const organizations = computed(() => organizationsQuery.data.value ?? []);

  const createErrorMessage = computed(() => {
    const responseData = createOrganizationMutation.error.value?.response?.data;

    return (
      responseData?.errors?.url?.[0] ??
      responseData?.message ??
      'Не удалось добавить организацию. Попробуйте ещё раз.'
    );
  });

  const listErrorMessage = computed(() => {
    return (
      organizationsQuery.error.value?.response?.data?.message ??
      'Не удалось загрузить организации.'
    );
  });

  function openCreateForm() {
    createOrganizationMutation.reset();
    isCreateFormOpen.value = true;
  }

  function closeCreateForm() {
    if (createOrganizationMutation.isPending.value) {
      return;
    }

    createOrganizationMutation.reset();
    isCreateFormOpen.value = false;
  }

  function createOrganization() {
    createOrganizationMutation.mutate(organizationUrl.value);
  }

  function retryOrganizations() {
    return organizationsQuery.refetch();
  }

  return {
    closeCreateForm,
    createErrorMessage,
    createOrganization,
    isCreateError: createOrganizationMutation.isError,
    isCreateFormOpen,
    isCreatePending: createOrganizationMutation.isPending,
    isListError: organizationsQuery.isError,
    isListFetching: organizationsQuery.isFetching,
    isListPending: organizationsQuery.isPending,
    listErrorMessage,
    openCreateForm,
    organizations,
    organizationUrl,
    retryOrganizations,
  };
}
