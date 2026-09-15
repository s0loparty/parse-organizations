import { computed, ref, toValue, watch } from 'vue';
import { keepPreviousData, useQuery } from '@tanstack/vue-query';

import http from '@/lib/http';

export function useOrganization(organizationId) {
  const reviewsPage = ref(1);

  const organizationQuery = useQuery(() => ({
    queryKey: ['organizations', toValue(organizationId)],
    queryFn: async () => {
      const response = await http.get(
        `/v1/organizations/${toValue(organizationId)}`,
      );

      return response.data.data;
    },
    refetchInterval: (query) =>
      query.state.data?.status === 'pending' ? 5_000 : false,
  }));

  const reviewsQuery = useQuery(() => ({
    queryKey: [
      'organizations',
      toValue(organizationId),
      'reviews',
      reviewsPage.value,
    ],
    queryFn: async () => {
      const response = await http.get(
        `/v1/organizations/${toValue(organizationId)}/reviews`,
        { params: { page: reviewsPage.value } },
      );

      return response.data;
    },
    placeholderData: keepPreviousData,
  }));

  const organization = computed(() => organizationQuery.data.value);
  const reviews = computed(() => reviewsQuery.data.value?.data ?? []);
  const reviewsMeta = computed(() => reviewsQuery.data.value?.meta);

  const organizationErrorMessage = computed(() => {
    if (organizationQuery.error.value?.response?.status === 404) {
      return 'Организация не найдена.';
    }

    return (
      organizationQuery.error.value?.response?.data?.message ??
      'Не удалось загрузить организацию.'
    );
  });

  const reviewsErrorMessage = computed(() => {
    return (
      reviewsQuery.error.value?.response?.data?.message ??
      'Не удалось загрузить отзывы.'
    );
  });

  function retryOrganization() {
    return organizationQuery.refetch();
  }

  function retryReviews() {
    return reviewsQuery.refetch();
  }

  watch(
    () => toValue(organizationId),
    () => {
      reviewsPage.value = 1;
    },
  );

  return {
    organization,
    organizationErrorMessage,
    isOrganizationError: organizationQuery.isError,
    isOrganizationFetching: organizationQuery.isFetching,
    isOrganizationPending: organizationQuery.isPending,
    isReviewsError: reviewsQuery.isError,
    isReviewsFetching: reviewsQuery.isFetching,
    isReviewsPending: reviewsQuery.isPending,
    reviews,
    reviewsErrorMessage,
    reviewsMeta,
    reviewsPage,
    retryOrganization,
    retryReviews,
  };
}
