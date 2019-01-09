let customerQuery = {
  fields: 'name,email,referral_source,joined_at,account_expired_at,status',
  filter: {
    account_expired_at: {
      not: null,
    },
    status: {
      in: [1, 2, 3],
    },
    joined_at: {
      min: '2018-09-01 00:00:00',
      max: '2018-09-07 23:59:59',
    },
  },
  sort: {
    joined_at: 'desc',
  },
  include: {
    groups: {
      fields: 'id,name,created_at',
      sort: {
        name: 'asc',
      },
      filter: {
        name: {
          like: 'developers',
        },
      },
    },
  },
};

axios
  .get('/api/v1/customers', {
    query: customerQuery,
  })
  .then(response => response.data)
  .then(customers => console.log(customers));
