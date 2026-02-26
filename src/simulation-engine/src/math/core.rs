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

    /// Computes the Spectral Radius ρ(J) using Power Iteration
    /// Real-world applications like page-rank or markov-chains use this.
    /// Returns the absolute value of the dominant eigenvalue.
    pub fn compute_spectral_radius(&self, jacobian: &Array2<f64>) -> f64 {
        let n = jacobian.dim().0;
        if n == 0 {
            return 0.0;
        }

        // Initialize a random vector (or vector of ones), normalize it
        let mut b_k = Array1::<f64>::ones(n);
        let mut b_k_norm = b_k.dot(&b_k).sqrt();
        if b_k_norm > 0.0 {
            b_k = b_k / b_k_norm;
        }

        let mut eigenvalue_approx: f64 = 0.0;
        let max_iterations = 100;
        let tolerance = 1e-8;

        for _ in 0..max_iterations {
            // Calculate the matrix-by-vector product J * b_k
            let b_k1 = jacobian.dot(&b_k);
            
            // Calculate the norm
            b_k_norm = b_k1.dot(&b_k1).sqrt();

            if b_k_norm < 1e-12 {
                // If the product is basically a zero vector, spectral radius is 0
                return 0.0;
            }

            // Rayleigh quotient approximation of the dominant eigenvalue
            // λ = (b_k^T * J * b_k) / (b_k^T * b_k) 
            // Since b_k is normalized (b_k^T * b_k = 1), λ = b_k^T * (J * b_k) = b_k.dot(&b_k1)
            let new_eigenvalue = b_k.dot(&b_k1);

            // Re-normalize the vector
            b_k = b_k1 / b_k_norm;

            // Check for convergence
            if (new_eigenvalue.abs() - eigenvalue_approx.abs()).abs() < tolerance {
                eigenvalue_approx = new_eigenvalue;
                break;
            }
            eigenvalue_approx = new_eigenvalue;
        }

        eigenvalue_approx.abs()
    }

    /// Verifies if J is a contraction map: ρ(J) <= 1 - delta
    pub fn verify_contraction(&self, jacobian: &Array2<f64>, delta: f64) -> Result<f64, String> {
        let rho = self.compute_spectral_radius(jacobian);
        let limit = 1.0 - delta;
        if rho > limit {
            return Err(format!("Dominant eigenvalue {:.4} violates stability margin {:.4} (delta: {:.4})", rho, limit, delta));
        }
        Ok(rho)
    }
}
