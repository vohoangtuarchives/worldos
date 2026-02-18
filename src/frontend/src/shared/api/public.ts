import { api } from "./client";

export const publicApi = {
  marketplace: {
    artifacts: () =>
      api.get<unknown>("/api/marketplace/artifacts").then((r) => (Array.isArray(r) ? r : [])),
  },
  vietnameseHeroes: {
    list: () =>
      api.get<unknown>("/api/vietnamese-heroes").then((r) => (Array.isArray(r) ? r : [])),
  },
};
