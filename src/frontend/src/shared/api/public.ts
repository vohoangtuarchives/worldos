import { api } from "./client";

export const publicApi = {
  marketplace: {
    artifacts: () =>
      api.get<unknown>("/api/marketplace/artifacts").then((r) => (Array.isArray(r) ? r : [])),
  },
  heroes: {
    list: () =>
      api.get<unknown>("/api/heroes").then((r) => (Array.isArray(r) ? r : [])),
  },
};
