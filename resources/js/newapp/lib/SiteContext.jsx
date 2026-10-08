import React, { createContext, useCallback, useContext, useMemo, useState } from 'react';

const SiteContext = createContext({
  site: null, user: null, mode: 'single_doctor', setUser: () => {},
  loginModal: null, openLogin: () => {}, closeLogin: () => {},
});

export function SiteProvider({ bootstrap, children }) {
  const [user, setUser] = useState(bootstrap?.user ?? null);
  // وقتی مقدار دارد، مودال سراسری ورود باز است؛ onDone پس از ورود موفق صدا زده می شود
  const [loginModal, setLoginModal] = useState(null);

  const openLogin = useCallback((opts = {}) => setLoginModal(opts), []);
  const closeLogin = useCallback(() => setLoginModal(null), []);

  const value = useMemo(() => ({
    site: bootstrap?.site ?? null,
    mode: bootstrap?.mode ?? 'single_doctor',
    images: bootstrap?.images ?? {},
    user,
    setUser,
    loginModal,
    openLogin,
    closeLogin,
  }), [bootstrap, user, loginModal, openLogin, closeLogin]);

  return <SiteContext.Provider value={value}>{children}</SiteContext.Provider>;
}

export function useSite() {
  return useContext(SiteContext);
}

export default SiteContext;
