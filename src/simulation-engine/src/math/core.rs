use ndarray::{Array1, Array2};

pub struct MathCore {
    pub alpha: f64,
    pub lambda: f64,
    pub eta: f64,
    pub beta: f64,
}

impl MathCore {
    pub fn new(alpha: f64, lambda: f64, eta: f64, beta: f64) -> Self {
        Self { alpha, lambda, eta, beta }
    }

    /// Evaluates the Effective Jacobian matrix J
    /// J = I + \alpha [ (A - I) - \lambda L - \eta I ]
    pub fn compute_jacobian(&self, a_matrix: &Array2<f64>, l_matrix: &Array2<f64>) -> Array2<f64> {
        let n = a_matrix.dim().0;
        let identity = Array2::<f64>::eye(n);

        // J = I + alpha * (A - I - lambda * L - eta * I)
        let mut j = identity.clone();
        j = j + self.alpha * (
            a_matrix - &identity 
            - self.lambda * l_matrix 
            - self.eta * &identity
        );
        j
    }

    /// Computes the next state x(t+1)
    /// x(t+1) = J * x(t) + \alpha * \beta * u(t)
    pub fn compute_next_state(
        &self, 
        jacobian: &Array2<f64>, 
        x_t: &Array1<f64>, 
        u_t: &Array1<f64>
    ) -> Array1<f64> {
        let mut x_next = jacobian.dot(x_t);
        x_next = x_next + (self.alpha * self.beta) * u_t;
        x_next
    }
}
